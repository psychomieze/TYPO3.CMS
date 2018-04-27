<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Adminpanel\Modules;

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

use TYPO3\CMS\Adminpanel\Service\ConfigurationService;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract base class for Core Admin Panel Modules containing helper methods
 *
 * @internal
 */
abstract class AbstractModule implements AdminPanelModuleInterface
{
    /**
     * @var string
     */
    protected $extResources = 'EXT:adminpanel/Resources/Private';

    /**
     * @var array
     */
    protected $subModules = [];
    protected $mainConfiguration;

    protected $configurationService;

    public function __construct()
    {
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $this->mainConfiguration = $this->configurationService->getMainConfiguration();
    }

    public function getSettings(): string
    {
        return '';
    }

    public function getIconIdentifier(): string {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function initializeModule(ServerRequest $request): void
    {
    }

    public function getContent(): string
    {
        return '';
    }

    /**
     * Returns true if the module is
     * -> either enabled via tsconfig admPanel.enable
     * -> or any setting is overridden
     * override is a way to use functionality of the admin panel without displaying the admin panel to users
     * for example: hidden records or pages can be displayed by default
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $identifier = $this->getIdentifier();
        $result = $this->isEnabledViaTsConfig();
        if ($this->mainConfiguration['override.'][$identifier] ?? false) {
            $result = (bool)$this->mainConfiguration['override.'][$identifier];
        }
        return $result;
    }


    /**
     * @inheritdoc
     */
    public function onSubmit(array $input): void
    {
    }

    /**
     * Translate given key
     *
     * @param string $key Key for a label in the $LOCAL_LANG array of "sysext/lang/Resources/Private/Language/locallang_tsfe.xlf
     * @param bool $convertWithHtmlspecialchars If TRUE the language-label will be sent through htmlspecialchars
     * @return string The value for the $key
     */
    protected function extGetLL($key, $convertWithHtmlspecialchars = true): string
    {
        $labelStr = $this->getLanguageService()->getLL($key);
        if ($convertWithHtmlspecialchars) {
            $labelStr = htmlspecialchars($labelStr, ENT_QUOTES | ENT_HTML5);
        }
        return $labelStr;
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
     * Returns LanguageService
     *
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Returns true if TSConfig admPanel.enable is set for this module (or all modules)
     *
     * @return bool
     */
    protected function isEnabledViaTsConfig(): bool
    {
        $result = false;
        $identifier = $this->getIdentifier();
        if (!empty($this->mainConfiguration['enable.']['all'])) {
            $result = true;
        } elseif (!empty($this->mainConfiguration['enable.'][$identifier])) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getJavaScriptFiles(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getCssFiles(): array
    {
        return [];
    }

    public function getShortInfo(): string
    {
        return '';
    }

    /**
     * @param array $subModules
     * @return void
     */
    public function setSubModules(array $subModules): void
    {
        $this->subModules = $subModules;
    }

    public function getSubModules(): array
    {
        return $this->subModules;
    }
}
