<?php
declare(strict_types=1);

namespace TYPO3\CMS\Adminpanel\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EditToolbarService
 *
 * @internal
 */
class EditToolbarService
{

    public function create()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $tsfe = $this->getTypoScriptFrontendController();
        //  If mod.newContentElementWizard.override is set, use that extension's create new content wizard instead:
        $tsConfig = BackendUtility::getModTSconfig($tsfe->page['uid'], 'mod');
        $moduleName = $tsConfig['properties']['newContentElementWizard.']['override'] ?? 'new_content_element';
        /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);
        $perms = $this->getBackendUser()->calcPerms($tsfe->page);
        $langAllowed = $this->getBackendUser()->checkLanguageAccess($tsfe->sys_language_uid);
        $id = $tsfe->id;
        $returnUrl = GeneralUtility::getIndpEnv('REQUEST_URI');
        $classes = 'typo3-adminPanel-btn typo3-adminPanel-btn-default';
        $output = [];
        $output[] = '<div class="typo3-adminPanel-form-group">';
        $output[] = '  <div class="typo3-adminPanel-btn-group" role="group">';

        // History
        $link = (string)$uriBuilder->buildUriFromRoute(
            'record_history',
            [
                'element' => 'pages:' . $id,
                'returnUrl' => $returnUrl,
            ]
        );
        $title = $this->extGetLL('edit_recordHistory');
        $output[] = '<a class="' . $classes . '" href="' . htmlspecialchars($link) . '#latest" title="' . $title . '">';
        $output[] = '  ' . $this->iconFactory->getIcon('actions-document-history-open', Icon::SIZE_SMALL)->render();
        $output[] = '</a>';

        // New Content
        if ($perms & Permission::CONTENT_EDIT && $langAllowed) {
            $linkParameters = [
                'id' => $id,
                'returnUrl' => $returnUrl,
            ];
            if (!empty($tsfe->sys_language_uid)) {
                $linkParameters['sys_language_uid'] = $tsfe->sys_language_uid;
            }
            $link = (string)$uriBuilder->buildUriFromRoute($moduleName, $linkParameters);
            $icon = $this->iconFactory->getIcon('actions-add', Icon::SIZE_SMALL)->render();
            $title = $this->extGetLL('edit_newContentElement');
            $output[] = '<a class="' . $classes . '" href="' . htmlspecialchars($link) . '" title="' . $title . '">';
            $output[] = '  ' . $icon;
            $output[] = '</a>';
        }

        // Move Page
        if ($perms & Permission::PAGE_EDIT) {
            $link = (string)$uriBuilder->buildUriFromRoute(
                'move_element',
                [
                    'table' => 'pages',
                    'uid' => $id,
                    'returnUrl' => $returnUrl,
                ]
            );
            $icon = $this->iconFactory->getIcon('actions-document-move', Icon::SIZE_SMALL)->render();
            $title = $this->extGetLL('edit_move_page');
            $output[] = '<a class="' . $classes . '" href="' . htmlspecialchars($link) . '" title="' . $title . '">';
            $output[] = '  ' . $icon;
            $output[] = '</a>';
        }

        // New Page
        if ($perms & Permission::PAGE_NEW) {
            $link = (string)$uriBuilder->buildUriFromRoute(
                'db_new',
                [
                    'id' => $id,
                    'pagesOnly' => 1,
                    'returnUrl' => $returnUrl,
                ]
            );
            $icon = $this->iconFactory->getIcon('actions-page-new', Icon::SIZE_SMALL)->render();
            $title = $this->extGetLL('edit_newPage');
            $output[] = '<a class="' . $classes . '" href="' . htmlspecialchars($link) . '" title="' . $title . '">';
            $output[] = '  ' . $icon;
            $output[] = '</a>';
        }

        // Edit Page
        if ($perms & Permission::PAGE_EDIT) {
            $link = (string)$uriBuilder->buildUriFromRoute(
                'record_edit',
                [
                    'edit[pages][' . $id . ']' => 'edit',
                    'noView' => 1,
                    'returnUrl' => $returnUrl,
                ]
            );
            $icon = $this->iconFactory->getIcon('actions-page-open', Icon::SIZE_SMALL)->render();
            $title = $this->extGetLL('edit_editPageProperties');
            $output[] = '<a class="' . $classes . '" href="' . htmlspecialchars($link) . '" title="' . $title . '">';
            $output[] = '  ' . $icon;
            $output[] = '</a>';
        }

        // Edit Page Overlay
        if ($perms & Permission::PAGE_EDIT && $tsfe->sys_language_uid && $langAllowed) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('pages');
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
            $row = $queryBuilder
                ->select('uid', 'pid', 't3ver_state')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq(
                        $GLOBALS['TCA']['pages']['ctrl']['transOrigPointerField'],
                        $queryBuilder->createNamedParameter($id, \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        $GLOBALS['TCA']['pages']['ctrl']['languageField'],
                        $queryBuilder->createNamedParameter($tsfe->sys_language_uid, \PDO::PARAM_INT)
                    )
                )
                ->setMaxResults(1)
                ->execute()
                ->fetch();
            $tsfe->sys_page->versionOL('pages', $row);
            if (is_array($row)) {
                $link = (string)$uriBuilder->buildUriFromRoute(
                    'record_edit',
                    [
                        'edit[pages][' . $row['uid'] . ']' => 'edit',
                        'noView' => 1,
                        'returnUrl' => $returnUrl,
                    ]
                );
                $icon = $this->iconFactory->getIcon('mimetypes-x-content-page-language-overlay', Icon::SIZE_SMALL)
                    ->render();
                $title = $this->extGetLL('edit_editPageOverlay');
                $output[] = '<a class="' .
                            $classes .
                            '" href="' .
                            htmlspecialchars($link) .
                            '" title="' .
                            $title .
                            '">';
                $output[] = '  ' . $icon;
                $output[] = '</a>';
            }
        }

        // Open list view
        if ($this->getBackendUser()->check('modules', 'web_list')) {
            $link = (string)$uriBuilder->buildUriFromRoute(
                'web_list',
                [
                    'id' => $id,
                    'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
                ]
            );
            $icon = $this->iconFactory->getIcon('actions-system-list-open', Icon::SIZE_SMALL)->render();
            $title = $this->extGetLL('edit_db_list');
            $output[] = '<a class="' . $classes . '" href="' . htmlspecialchars($link) . '" title="' . $title . '">';
            $output[] = '  ' . $icon;
            $output[] = '</a>';
        }

        $output[] = '  </div>';
        $output[] = '</div>';
        return implode('', $output);
    }

    /**
     * Translate given key
     *
     * @param string $key Key for a label in the $LOCAL_LANG array of "sysext/lang/Resources/Private/Language/locallang_tsfe.xlf
     * @param bool $convertWithHtmlspecialchars If TRUE the language-label will be sent through htmlspecialchars
     * @deprecated Since TYPO3 v9 - only used in deprecated methods
     * @return string The value for the $key
     */
    protected function extGetLL($key, $convertWithHtmlspecialchars = true)
    {
        $labelStr = $this->getLanguageService()->getLL($key);
        if ($convertWithHtmlspecialchars) {
            $labelStr = htmlspecialchars($labelStr);
        }
        return $labelStr;
    }
    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Returns the current BE user.
     *
     * @return \TYPO3\CMS\Backend\FrontendBackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns LanguageService
     *
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}
