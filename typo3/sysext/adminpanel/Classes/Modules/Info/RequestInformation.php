<?php
declare(strict_types=1);

namespace TYPO3\CMS\Adminpanel\Modules\Info;


use TYPO3\CMS\Adminpanel\Modules\AdminPanelSubModuleInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class RequestInformation implements AdminPanelSubModuleInterface
{

    /**
     * Sub-Module content as rendered HTML
     *
     * @return string
     */
    public function getContent(): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $templateNameAndPath = 'EXT:adminpanel/Resources/Private/Templates/Modules/Info/RequestInformation.html';
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateNameAndPath));
        $view->setPartialRootPaths(['EXT:adminpanel/Resources/Private/Partials']);

        $view->assignMultiple(
            [
                'post' => $_POST,
                'get' => $_GET,
                'cookie' => $_COOKIE,
                'session' => $_SESSION,
                'server' => $_SERVER,
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
        return 'info-request';
    }

    /**
     * Sub-Module label
     *
     * @return string
     */
    public function getLabel(): string
    {
       return 'REQUEST';
    }

    /**
     * Settings as HTML form elements (without wrapping form tag or save button)
     *
     * @return string
     */
    public function getSettings(): string
    {
        return '';
    }

    /**
     * Initialize the module - runs early in a TYPO3 request
     *
     * @param \TYPO3\CMS\Core\Http\ServerRequest $request
     */
    public function initializeModule(ServerRequest $request): void
    {
        // TODO: Implement initializeModule() method.
    }
}
