<?php

/**
 * Definitions for routes provided by EXT:adminpanel
 * Contains all AJAX-based routes for entry points
 */
return [
    'adminpanel_saveForm' => [
        'path' => '/adminpanel/form/save',
        'target' => \TYPO3\CMS\Adminpanel\Controller\EidController::class . '::saveDataAction'
    ],
];
