<?php
declare(strict_types=1);

namespace TYPO3\CMS\Adminpanel\Service;

use TYPO3\CMS\Adminpanel\Modules\AdminPanelModuleInterface;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ModuleLoader
{


    /**
     * Validates, sorts and initiates the registered modules
     *
     * @throws \RuntimeException
     * @return array<AdminPanelModuleInterface>
     */
    public function getModulesFromConfiguration(): array
    {
        $modules = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['adminpanel']['modules'] ?? [];
        if (empty($modules)) {
            return [];
        }
        foreach ($modules as $identifier => $configuration) {
            if (empty($configuration) || !is_array($configuration)) {
                throw new \RuntimeException(
                    'Missing configuration for module "' . $identifier . '".',
                    1519490105
                );
            }
            if (!is_string($configuration['module']) ||
                empty($configuration['module']) ||
                !class_exists($configuration['module']) ||
                !is_subclass_of(
                    $configuration['module'],
                    AdminPanelModuleInterface::class
                )) {
                throw new \RuntimeException(
                    'The module "' .
                    $identifier .
                    '" defines an invalid module class. Ensure the class exists and implements the "' .
                    AdminPanelModuleInterface::class .
                    '".',
                    1519490112
                );
            }
        }

        $orderedModules = GeneralUtility::makeInstance(DependencyOrderingService::class)->orderByDependencies(
            $modules
        );

        $finalModulesArr = [];
        foreach ($orderedModules as $module) {
            $finalModulesArr[] = GeneralUtility::makeInstance($module['module']);
        }

        return $finalModulesArr;
    }
}
