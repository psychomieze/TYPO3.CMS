<?php

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['adminpanel']['modules'] = [
    'preview' => [
        'module' => \TYPO3\CMS\Adminpanel\Modules\PreviewModule::class,
        'before' => ['cache'],
    ],
    'cache' => [
        'module' => \TYPO3\CMS\Adminpanel\Modules\CacheModule::class,
        'after' => ['preview'],
    ],
    'edit' => [
        'module' => \TYPO3\CMS\Adminpanel\Modules\EditModule::class,
        'after' => ['cache'],
    ],
    'tsdebug' => [
        'module' => \TYPO3\CMS\Adminpanel\Modules\TsDebugModule::class,
        'after' => ['edit'],
    ],
    'info' => [
        'module' => \TYPO3\CMS\Adminpanel\Modules\InfoModule::class,
        'after' => ['tsdebug'],
    ],
];

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['adminPanel_save'] = \TYPO3\CMS\Adminpanel\Controller\EidController::class .
                                                                      '::saveDataAction';

