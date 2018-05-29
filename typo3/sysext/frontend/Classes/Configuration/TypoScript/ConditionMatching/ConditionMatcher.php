<?php

namespace TYPO3\CMS\Frontend\Configuration\TypoScript\ConditionMatching;

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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\TypoScript\ConditionMatching\AbstractConditionMatcher;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Matching TypoScript conditions for frontend disposal.
 *
 * Used with the TypoScript parser. Matches browserinfo
 * and IP numbers for use with templates.
 */
class ConditionMatcher extends AbstractConditionMatcher
{
    /**
     * Evaluates a TypoScript condition given as input,
     * eg. "[browser=net][...(other conditions)...]"
     *
     * @param string $string The condition to match against its criteria.
     * @return bool Whether the condition matched
     * @see \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser::parse()
     * @throws \TYPO3\CMS\Core\Configuration\TypoScript\Exception\InvalidTypoScriptConditionException
     */
    protected function evaluateCondition($string)
    {
        list($key, $value) = GeneralUtility::trimExplode('=', $string, false, 2);
        $result = $this->evaluateConditionCommon($key, $value);

        if (is_bool($result)) {
            return $result;
        }
        switch ($key) {
            case 'usergroup':
                $groupList = $this->getGroupList();
                // '0,-1' is the default usergroups when not logged in!
                if ($groupList !== '0,-1') {
                    $values = GeneralUtility::trimExplode(',', $value, true);
                    foreach ($values as $test) {
                        if ($test === '*' || GeneralUtility::inList($groupList, $test)) {
                            return true;
                        }
                    }
                }
                break;
            case 'treeLevel':
                $values = GeneralUtility::trimExplode(',', $value, true);
                $treeLevel = count($this->rootline) - 1;
                foreach ($values as $test) {
                    if ($test == $treeLevel) {
                        return true;
                    }
                }
                break;
            case 'PIDupinRootline':
            case 'PIDinRootline':
                $values = GeneralUtility::trimExplode(',', $value, true);
                if ($key === 'PIDinRootline' || !in_array($this->pageId, $values)) {
                    foreach ($values as $test) {
                        foreach ($this->rootline as $rlDat) {
                            if ($rlDat['uid'] == $test) {
                                return true;
                            }
                        }
                    }
                }
                break;
            case 'site':
                $siteLanguage = $this->getCurrentSiteLanguage();
                if ($siteLanguage instanceof SiteLanguage) {
                    $site = $siteLanguage->getSite();
                    $values = GeneralUtility::trimExplode(',', $value, true);
                    foreach ($values as $test) {
                        $point = strcspn($test, '=');
                        $testValue = substr($test, $point + 1);
                        $testValue = trim($testValue);
                        $theVarName = trim(substr($test, 0, $point));
                        $methodName = 'get' . ucfirst($theVarName);
                        if (method_exists($site, $methodName)) {
                            $sitePropertyValue = call_user_func([$site, $methodName]);
                            // loose check on purpose in order to check for integer values
                            if ($testValue == $sitePropertyValue) {
                                return true;
                            }
                        }
                    }
                }
                break;
            case 'siteLanguage':
                $siteLanguage = $this->getCurrentSiteLanguage();
                if ($siteLanguage instanceof SiteLanguage) {
                    $values = GeneralUtility::trimExplode(',', $value, true);
                    foreach ($values as $test) {
                        $point = strcspn($test, '=');
                        $testValue = substr($test, $point + 1);
                        $testValue = trim($testValue);
                        $theVarName = trim(substr($test, 0, $point));
                        $methodName = 'get' . ucfirst($theVarName);
                        if (method_exists($siteLanguage, $methodName)) {
                            $languagePropertyValue = call_user_func([$siteLanguage, $methodName]);
                            // loose check on purpose in order to check for integer values
                            if ($testValue == $languagePropertyValue) {
                                return true;
                            }
                        }
                    }
                }
                break;
            default:
                $conditionResult = $this->evaluateCustomDefinedCondition($string);
                if ($conditionResult !== null) {
                    return $conditionResult;
                }
        }

        return false;
    }

    /**
     * Returns GP / ENV / TSFE / session vars
     *
     * @example GP:L
     * @example TSFE:fe_user|sesData|foo|bar
     * @example TSFE:id
     * @example ENV:HTTP_HOST
     *
     * @param string $var Identifier
     * @return mixed|null The value of the variable pointed to or NULL if variable did not exist
     */
    protected function getVariable($var)
    {
        $vars = explode(':', $var, 2);
        $val = $this->getVariableCommon($vars);
        if ($val === null) {
            $splitAgain = explode('|', $vars[1], 2);
            $k = trim($splitAgain[0]);
            if ($k) {
                switch ((string)trim($vars[0])) {
                    case 'TSFE':
                        if (strpos($vars[1], 'fe_user|sesData|') === 0) {
                            trigger_error(
                                'Condition on TSFE|fe_user|sesData is deprecated and will be removed in TYPO3 CMS 10',
                                E_USER_DEPRECATED
                            );
                            $val = $this->getSessionVariable(substr($vars[1], 16));
                        } else {
                            $val = $this->getGlobal('TSFE|' . $vars[1]);
                        }
                        break;
                    case 'session':
                        $val = $this->getSessionVariable($vars[1]);
                        break;
                    default:
                }
            }
        }
        return $val;
    }

    /**
     * Return variable from current frontend user session
     *
     * @param string $var Session key
     * @return mixed|null The value of the variable pointed to or NULL if variable did not exist
     */
    protected function getSessionVariable(string $var)
    {
        $retVal = null;
        $keyParts = explode('|', $var);
        $sessionKey = array_shift($keyParts);
        $tsfe = $this->getTypoScriptFrontendController();
        if ($tsfe && is_object($tsfe->fe_user)) {
            $retVal = $tsfe->fe_user->getSessionData($sessionKey);
            foreach ($keyParts as $keyPart) {
                if (is_object($retVal)) {
                    $retVal = $retVal->{$keyPart};
                } elseif (is_array($retVal)) {
                    $retVal = $retVal[$keyPart];
                } else {
                    break;
                }
            }
        }
        return $retVal;
    }

    /**
     * Get the usergroup list of the current user.
     *
     * @return string The usergroup list of the current user
     */
    protected function getGroupList()
    {
        return $this->getTypoScriptFrontendController()->gr_list;
    }

    /**
     * Determines the current page Id.
     *
     * @return int The current page Id
     */
    protected function determinePageId()
    {
        return (int)($this->getTypoScriptFrontendController()->id ?? 0);
    }

    /**
     * Gets the properties for the current page.
     *
     * @return array The properties for the current page.
     */
    protected function getPage()
    {
        return $this->getTypoScriptFrontendController()->page;
    }

    /**
     * Determines the rootline for the current page.
     *
     * @return array The rootline for the current page.
     */
    protected function determineRootline()
    {
        return (array)$this->getTypoScriptFrontendController()->tmpl->rootLine;
    }

    /**
     * Get the id of the current user.
     *
     * @return int The id of the current user
     */
    protected function getUserId()
    {
        return $this->getTypoScriptFrontendController()->fe_user->user['uid'];
    }

    /**
     * Determines if a user is logged in.
     *
     * @return bool Determines if a user is logged in
     */
    protected function isUserLoggedIn()
    {
        return (bool)$this->getTypoScriptFrontendController()->loginUser;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Returns the currently configured "site language" if a site is configured (= resolved) in the current request.
     *
     * @internal
     */
    protected function getCurrentSiteLanguage(): ?SiteLanguage
    {
        if ($GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface
            && $GLOBALS['TYPO3_REQUEST']->getAttribute('language') instanceof SiteLanguage) {
            return $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
        }
        return null;
    }
}
