<?php
declare(strict_types=1);

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

use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Admin Panel TypoScript Debug Module
 */
class TsDebugModule extends AbstractModule
{
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
                'isEnabled' => (int)$tsfeAdminConfig['display_tsdebug'],
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

    public function getIconIdentifier(): string
    {
        return 'mimetypes-x-content-template-static';
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return 'tsdebug';
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        $locallangFileAndPath = 'LLL:' . $this->extResources . '/Language/locallang_tsdebug.xlf:module.label';
        return $this->getLanguageService()->sL($locallangFileAndPath);
    }

    public function getShortInfo(): string
    {
        $messageCount = 0;
        foreach ($this->getTimeTracker()->tsStackLog as $log) {
            $messageCount += count($log['message'] ?? []);
        }
        $locallangFileAndPath = 'LLL:' . $this->extResources . '/Language/locallang_tsdebug.xlf:module.shortinfo';
        return sprintf($this->getLanguageService()->sL($locallangFileAndPath), $messageCount);
    }

    /**
     * @inheritdoc
     */
    public function initializeModule(): void
    {
        $typoScriptFrontend = $this->getTypoScriptFrontendController();
        $typoScriptFrontend->forceTemplateParsing = (bool)$this->getConfigurationOption('forceTemplateParsing');
        if ($typoScriptFrontend->forceTemplateParsing) {
            $typoScriptFrontend->set_no_cache('Admin Panel: Force template parsing', true);
        }
        $this->getTimeTracker()->LR = (bool)$this->getConfigurationOption('LR');
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

    /**
     * Renders the TypoScript log as string
     *
     * @return string
     */
    private function renderTypoScriptLog(): string
    {
        $timeTracker = $this->getTimeTracker();
        $timeTracker->printConf['flag_tree'] = $this->getConfigurationOption('tree');
        $timeTracker->printConf['allTime'] = $this->getConfigurationOption('displayTimes');
        $timeTracker->printConf['flag_messages'] = $this->getConfigurationOption('displayMessages');
        $timeTracker->printConf['flag_content'] = $this->getConfigurationOption('displayContent');
        return $timeTracker->printTSlog();
    }

    /**
     * @return array
     */
    public function getJavaScriptFiles(): array
    {
        return ['EXT:adminpanel/Resources/Public/JavaScript/Modules/TsDebug.js'];
    }
}
