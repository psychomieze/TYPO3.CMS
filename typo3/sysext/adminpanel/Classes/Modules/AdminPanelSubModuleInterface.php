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

use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * Interface for admin panel sub modules registered via EXTCONF
 *
 * @internal until API is stable
 */
interface AdminPanelSubModuleInterface
{
    /**
     * Sub-Module content as rendered HTML
     *
     * @return string
     */
    public function getContent(): string;


    /**
     * Identifier for this Sub-module,
     * for example "preview" or "cache"
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Sub-Module label
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Settings as HTML form elements (without wrapping form tag or save button)
     *
     * @return string
     */
    public function getSettings(): string;

    /**
     * Initialize the module - runs early in a TYPO3 request
     *
     * @param \TYPO3\CMS\Core\Http\ServerRequest $request
     */
    public function initializeModule(ServerRequest $request): void;
}
