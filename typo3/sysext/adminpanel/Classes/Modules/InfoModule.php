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

use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Admin Panel Info Module
 */
class InfoModule extends AbstractModule
{
    public function getIconIdentifier(): string
    {
        return 'actions-document-info';
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return 'info';
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        $locallangFileAndPath = 'LLL:' . $this->extResources . '/Language/locallang_info.xlf:module.label';
        return $this->getLanguageService()->sL($locallangFileAndPath);
    }

    public function getShortInfo(): string
    {
        $locallangFileAndPath = 'LLL:' . $this->extResources . '/Language/locallang_info.xlf:module.shortinfo';
        $parseTime = $this->getTimeTracker()->getParseTime();
        return sprintf($this->getLanguageService()->sL($locallangFileAndPath), $parseTime);
    }

    /**
     * @return TimeTracker
     */
    protected function getTimeTracker(): TimeTracker
    {
        return GeneralUtility::makeInstance(TimeTracker::class);
    }
}
