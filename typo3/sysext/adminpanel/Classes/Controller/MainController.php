<?php
declare(strict_types=1);

namespace TYPO3\CMS\Adminpanel\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Adminpanel\Modules\AdminPanelModuleInterface;
use TYPO3\CMS\Adminpanel\Modules\AdminPanelSubModuleInterface;
use TYPO3\CMS\Adminpanel\View\AdminPanelView;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Main controller for the admin panel
 *
 * @internal
 */
class MainController implements SingletonInterface
{
    /**
     * @var array<AdminPanelModuleInterface>
     */
    protected $modules = [];

    /**
     * Initializes settings for the admin panel.
     *
     * @param \TYPO3\CMS\Core\Http\ServerRequest $request
     */
    public function initialize(ServerRequest $request): void
    {
        $this->modules = $this->validateSortAndInitializeModules(
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['adminpanel']['modules'] ?? []
        );
        $this->saveConfiguration();

        if ($this->isAdminPanelActivated()) {
            foreach ($this->modules as $module) {
                if ($module->isEnabled()) {
                    $subModules = $this->validateSortAndInitializeSubModules(
                        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['adminpanel']['modules'][$module->getIdentifier()]['submodules'] ?? []
                    );
                    foreach ($subModules as $subModule) {
                        $subModule->initializeModule($request);
                    }
                    $module->setSubModules($subModules);
                    $module->initializeModule($request);
                }
            }
        }
    }

    /**
     * Renders the panel - Is currently called via RenderHook in postProcessOutput
     *
     * @return string
     */
    public function render(): string
    {
        // legacy handling
        $adminPanelView = GeneralUtility::makeInstance(AdminPanelView::class);
        $hookObjectContent = $adminPanelView->callDeprecatedHookObject();
        // end legacy handling

        $resources = $this->getResources();

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $templateNameAndPath = 'EXT:adminpanel/Resources/Private/Templates/Main.html';
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateNameAndPath));
        $view->setPartialRootPaths(['EXT:adminpanel/Resources/Private/Partials']);
        $view->setLayoutRootPaths(['EXT:adminpanel/Resources/Private/Layouts']);

        $view->assignMultiple(
            [
                'toggleActiveUrl' => $this->generateBackendUrl('ajax_adminPanel_toggle'),
                'resources' => $resources,
                'adminPanelActive' => $this->isAdminPanelActivated(),
            ]
        );
        if ($this->isAdminPanelActivated()) {
            $moduleResources = $this->getAdditionalResourcesForModules($this->modules);
            $view->assignMultiple(
                [
                    'modules' => $this->modules,
                    'hookObjectContent' => $hookObjectContent,
                    'saveUrl' => $this->generateBackendUrl('ajax_adminPanel_saveForm'),
                    'moduleResources' => $moduleResources,
                ]
            );
        }

        return $view->render();
    }

    protected function generateBackendUrl(string $route): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string)$uriBuilder->buildUriFromRoute($route);
    }

    protected function getAdditionalResourcesForModules(array $modules): array
    {
        $result = [
            'js' => '',
            'css' => '',
        ];
        /** @var AdminPanelModuleInterface $module */
        foreach ($modules as $module) {
            foreach ($module->getJavaScriptFiles() as $file) {
                $result['js'] .= $this->getJsTag($file);
            }
            foreach ($module->getCssFiles() as $file) {
                $result['css'] .= $this->getCssTag($file);
            }
        }
        return $result;
    }

    /**
     * Returns a link tag with the admin panel stylesheet
     * defined using TBE_STYLES
     *
     * @return string
     */
    protected function getAdminPanelStylesheet(): string
    {
        $result = '';
        if (!empty($GLOBALS['TBE_STYLES']['stylesheets']['admPanel'])) {
            $stylesheet = GeneralUtility::locationHeaderUrl($GLOBALS['TBE_STYLES']['stylesheets']['admPanel']);
            $result = '<link rel="stylesheet" type="text/css" href="' .
                      htmlspecialchars($stylesheet, ENT_QUOTES | ENT_HTML5) . '" />';
        }
        return $result;
    }

    /**
     * Returns the current BE user.
     *
     * @return FrontendBackendUserAuthentication
     */
    protected function getBackendUser(): FrontendBackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @param $cssFileLocation
     * @return string
     */
    protected function getCssTag($cssFileLocation): string
    {
        $css = '<link type="text/css" rel="stylesheet" href="' .
               htmlspecialchars(
                   PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName($cssFileLocation)),
                   ENT_QUOTES | ENT_HTML5
               ) .
               '" media="all" />';
        return $css;
    }

    /**
     * @param $jsFileLocation
     * @return string
     */
    protected function getJsTag($jsFileLocation): string
    {
        $js = '<script type="text/javascript" src="' .
              htmlspecialchars(
                  PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName($jsFileLocation)),
                  ENT_QUOTES | ENT_HTML5
              ) .
              '"></script>';
        return $js;
    }

    /**
     * Returns LanguageService
     *
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    protected function getResources(): string
    {
        $jsFileLocation = 'EXT:adminpanel/Resources/Public/JavaScript/AdminPanel.js';
        $js = $this->getJsTag($jsFileLocation);
        $cssFileLocation = 'EXT:adminpanel/Resources/Public/Css/adminpanel.css';
        $css = $this->getCssTag($cssFileLocation);

        return $css . $this->getAdminPanelStylesheet() . $js;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Returns true if admin panel was activated
     * (switched "on" via GUI)
     *
     * @return bool
     */
    protected function isAdminPanelActivated(): bool
    {
        return (bool)($this->getBackendUser()->uc['TSFE_adminConfig']['display_top'] ?? false);
    }

    /**
     * Save admin panel configuration to backend user UC
     */
    protected function saveConfiguration(): void
    {
        $input = GeneralUtility::_GP('TSFE_ADMIN_PANEL');
        $beUser = $this->getBackendUser();
        if (is_array($input)) {
            // Setting
            $beUser->uc['TSFE_adminConfig'] = array_merge(
                !is_array($beUser->uc['TSFE_adminConfig']) ? [] : $beUser->uc['TSFE_adminConfig'],
                $input
            );
            unset($beUser->uc['TSFE_adminConfig']['action']);

            foreach ($this->modules as $module) {
                if ($module->isEnabled() && $module->isOpen()) {
                    $module->onSubmit($input);
                }
            }
            // Saving
            $beUser->writeUC();
            // Flush fluid template cache
            $cacheManager = new CacheManager();
            $cacheManager->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
            $cacheManager->getCache('fluid_template')->flush();
        }
    }

    protected function validateSortAndInitializeSubModules(array $modules): array {
        return $this->validateSortAndInitializeModules($modules, 'sub');
    }

    /**
     * Validates, sorts and initiates the registered modules
     *
     * @param array $modules
     * @param string $type
     * @return array
     */
    protected function validateSortAndInitializeModules(array $modules, string $type = 'main'): array
    {
        if (empty($modules)) {
            return [];
        }
        foreach ($modules as $identifier => $configuration) {
            if (empty($configuration) || !is_array($configuration)) {
                throw new \RuntimeException(
                    'Missing configuration for module "' . $identifier . '".',
                    1519490105
                );
            }
            if (!is_string($configuration['module']) ||
                empty($configuration['module']) ||
                !class_exists($configuration['module']) ||
                !is_subclass_of(
                    $configuration['module'],
                    ($type === 'main' ? AdminPanelModuleInterface::class : AdminPanelSubModuleInterface::class)
                )
            ) {
                throw new \RuntimeException(
                    'The module "' .
                    $identifier .
                    '" defines an invalid module class. Ensure the class exists and implements the "' .
                    AdminPanelModuleInterface::class .
                    '".',
                    1519490112
                );
            }
        }

        $orderedModules = GeneralUtility::makeInstance(DependencyOrderingService::class)->orderByDependencies(
            $modules
        );

        $moduleInstances = [];
        foreach ($orderedModules as $module) {
            $moduleInstances[] = GeneralUtility::makeInstance($module['module']);
        }
        return $moduleInstances;
    }

}
