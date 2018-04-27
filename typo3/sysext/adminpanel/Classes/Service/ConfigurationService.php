<?php
declare(strict_types=1);

namespace TYPO3\CMS\Adminpanel\Service;


use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;

class ConfigurationService implements SingletonInterface
{
    /**
     * @var array
     */
    protected $mainConfiguration;

    public function __construct()
    {
        $this->mainConfiguration = $this->getBackendUser()->getTSConfigProp('admPanel');
    }

    public function getMainConfiguration(): array
    {
        return $this->mainConfiguration;
    }

    /**
     * Helper method to return configuration options
     * Checks User TSConfig overrides and current backend user session
     *
     * @param string $identifier
     * @param string $option
     * @return string
     */
    public function getConfigurationOption(string $identifier, string $option): string
    {
        $beUser = $this->getBackendUser();

        if ($option && isset($this->mainConfiguration['override.'][$identifier . '.'][$option])) {
            $returnValue = $this->mainConfiguration['override.'][$identifier . '.'][$option];
        } else {
            $returnValue = $beUser->uc['TSFE_adminConfig'][$identifier . '_' . $option] ?? '';
        }

        return (string)$returnValue;
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

}
