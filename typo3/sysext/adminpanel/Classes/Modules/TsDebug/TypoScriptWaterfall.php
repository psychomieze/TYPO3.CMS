<?php
declare(strict_types=1);

namespace TYPO3\CMS\Adminpanel\Modules\TsDebug;

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

use TYPO3\CMS\Adminpanel\Modules\AdminPanelSubModuleInterface;
use TYPO3\CMS\Adminpanel\Service\ConfigurationService;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TypoScriptWaterfall implements AdminPanelSubModuleInterface
{

    private $configurationService;

    public function __construct()
    {
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
    }

    /**
     * @var string
     */
    protected $extResources = 'EXT:adminpanel/Resources/Private';

    /**
     * Creates the content for the "tsdebug" section ("module") of the Admin Panel
     *
     * @return string HTML content for the section. Consists of a string with table-rows with four columns.
     */
    public function getContent(): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $templateNameAndPath = $this->extResources . '/Templates/Modules/TsDebug.html';
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateNameAndPath));
        $view->setPartialRootPaths([$this->extResources . '/Partials']);

        $tsfeAdminConfig = $this->getBackendUser()->uc['TSFE_adminConfig'];
        $view->assignMultiple(
            [
                'tree' => (int)$tsfeAdminConfig['tsdebug_tree'],
                'display' => [
                    'times' => (int)$tsfeAdminConfig['tsdebug_displayTimes'],
                    'messages' => (int)$tsfeAdminConfig['tsdebug_displayMessages'],
                    'content' => (int)$tsfeAdminConfig['tsdebug_displayContent'],
                ],
                'trackContentRendering' => (int)$tsfeAdminConfig['tsdebug_LR'],
                'forceTemplateParsing' => (int)$tsfeAdminConfig['tsdebug_forceTemplateParsing'],
                'typoScriptLog' => $this->renderTypoScriptLog(),
            ]
        );

        return $view->render();
    }

    /**
     * Identifier for this Sub-module,
     * for example "preview" or "cache"
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'typoscript-waterfall';
    }

    /**
     * Sub-Module label
     *
     * @return string
     */
    public function getLabel(): string
    {
        // @todo
        return 'Don\'t go chasing waterfalls...';
    }

    /**
     * Settings as HTML form elements (without wrapping form tag or save button)
     *
     * @return string
     */
    public function getSettings(): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $templateNameAndPath = $this->extResources . '/Templates/Modules/TsDebug/TypoScriptWaterFallSettings.html';
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateNameAndPath));
        $view->setPartialRootPaths([$this->extResources . '/Partials']);

        $tsfeAdminConfig = $this->getBackendUser()->uc['TSFE_adminConfig'];
        $view->assignMultiple(
            [
                'tree' => (int)$tsfeAdminConfig['tsdebug_tree'],
                'display' => [
                    'times' => (int)$tsfeAdminConfig['tsdebug_displayTimes'],
                    'messages' => (int)$tsfeAdminConfig['tsdebug_displayMessages'],
                    'content' => (int)$tsfeAdminConfig['tsdebug_displayContent'],
                ],
                'trackContentRendering' => (int)$tsfeAdminConfig['tsdebug_LR'],
                'forceTemplateParsing' => (int)$tsfeAdminConfig['tsdebug_forceTemplateParsing'],
                'typoScriptLog' => $this->renderTypoScriptLog(),
            ]
        );

        return $view->render();
    }

    /**
     * @inheritdoc
     */
    public function initializeModule(ServerRequest $request): void
    {
        $typoScriptFrontend = $this->getTypoScriptFrontendController();
        $typoScriptFrontend->forceTemplateParsing = (bool)$this->configurationService->getConfigurationOption('tsdebug','forceTemplateParsing');
        if ($typoScriptFrontend->forceTemplateParsing) {
            $typoScriptFrontend->set_no_cache('Admin Panel: Force template parsing', true);
        }
        $this->getTimeTracker()->LR = (bool)$this->configurationService->getConfigurationOption('tsdebug','LR');
    }

    /**
     * Returns the current BE user.
     *
     * @return BackendUserAuthentication|FrontendBackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Renders the TypoScript log as string
     *
     * @return string
     */
    protected function renderTypoScriptLog(): string
    {
        $timeTracker = $this->getTimeTracker();
        $timeTracker->printConf['flag_tree'] = $this->configurationService->getConfigurationOption('tsdebug','tree');
        $timeTracker->printConf['allTime'] = $this->configurationService->getConfigurationOption('tsdebug','displayTimes');
        $timeTracker->printConf['flag_messages'] = $this->configurationService->getConfigurationOption('tsdebug','displayMessages');
        $timeTracker->printConf['flag_content'] = $this->configurationService->getConfigurationOption('tsdebug','displayContent');
        return $timeTracker->printTSlog();
    }

    /**
     * @return TimeTracker
     */
    protected function getTimeTracker(): TimeTracker
    {
        return GeneralUtility::makeInstance(TimeTracker::class);
    }


    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
