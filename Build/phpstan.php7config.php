<?php

declare(strict_types=1);

$config = [];

if (PHP_MAJOR_VERSION === 7) {
    $config['parameters']['ignoreErrors'] = [
        '#Class GdImage not found.#',
        [
            'message' => '#^Parameter \\#1 \\$sem_identifier of function sem_release expects resource, resource\\|SysvSemaphore given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/core/Classes/Locking/SemaphoreLockStrategy.php',
            'count' => 1,
        ],
        [
            'message' => '#^Parameter \\#1 \\$sem_identifier of function sem_remove expects resource, resource\\|SysvSemaphore given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/core/Classes/Locking/SemaphoreLockStrategy.php',
            'count' => 1,
        ],
        [
            'message' => '#^Parameter \\#2 \\$algo of function password_hash expects string\\|null, int\\|string\\|null given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/core/Classes/Crypto/PasswordHashing/AbstractArgon2PasswordHash.php',
            'count' => 1
        ],
        [
            'message' => '#^Parameter \\#2 \\$algo of function password_needs_rehash expects string\\|null, int\\|string\\|null given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/core/Classes/Crypto/PasswordHashing/AbstractArgon2PasswordHash.php',
            'count' => 1
        ],
        [
            'message' => '#^Parameter \\#1 \\$im of function imagecolorallocate expects resource, resource\\|false given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/install/Classes/Controller/EnvironmentController.php',
            'count' => 6
        ],
        [
            'message' => '#^Parameter \\#1 \\$im of function imagefilledrectangle expects resource, resource\\|false given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/install/Classes/Controller/EnvironmentController.php',
            'count' => 4
        ],
        [
            'message' => '#^Parameter \\#1 \\$im of function imagegif expects resource, resource\\|false given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/install/Classes/Controller/EnvironmentController.php',
            'count' => 1
        ],
        [
            'message' => '#^Parameter \\#1 \\$im of function imagettftext expects resource, resource\\|false given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/install/Classes/Controller/EnvironmentController.php',
            'count' => 1
        ],
        [
            'message' => '#^Parameter \\#6 \\$col of function imagefilledrectangle expects int, int\\|false given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/install/Classes/Controller/EnvironmentController.php',
            'count' => 4
        ],
        [
            'message' => '#^Parameter \\#6 \\$col of function imagettftext expects int, int\\|false given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/install/Classes/Controller/EnvironmentController.php',
            'count' => 1
        ],
        [
            'message' => '#^Parameter \\#1 \\$im of function imagedestroy expects resource, resource\\|false given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/install/Classes/SystemEnvironment/Check.php',
            'count' => 3
        ],
        [
            'message' => '#^Parameter \\#2 \\$col of function imagecolortransparent expects int, int\\|false given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/frontend/Classes/Imaging/GifBuilder.php',
            'count' => 1
        ],
        [
            'message' => '#^Parameter \\#1 \\$parser of function xml_parse expects resource, XMLParser given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/extensionmanager/Classes/Utility/Parser/ExtensionXmlPushParser.php',
            'count' => 1
        ],
        [
            'message' => '#^Parameter \\#1 \\$parser of function xml_parser_free expects resource, XMLParser given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/extensionmanager/Classes/Utility/Parser/ExtensionXmlPushParser.php',
            'count' => 1
        ],
        [
            'message' => '#^Parameter \\#1 \\$parser of function xml_parser_set_option expects resource, XMLParser given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/extensionmanager/Classes/Utility/Parser/ExtensionXmlPushParser.php',
            'count' => 3
        ],
        [
            'message' => '#^Parameter \\#1 \\$parser of function xml_set_character_data_handler expects resource, XMLParser given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/extensionmanager/Classes/Utility/Parser/ExtensionXmlPushParser.php',
            'count' => 1
        ],
        [
            'message' => '#^Parameter \\#1 \\$parser of function xml_set_element_handler expects resource, XMLParser given\\.$#',
            'path' => '%currentWorkingDirectory%/typo3/sysext/extensionmanager/Classes/Utility/Parser/ExtensionXmlPushParser.php',
            'count' => 1
        ],
    ];
}

return $config;
