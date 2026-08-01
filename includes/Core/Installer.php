<?php

declare(strict_types=1);

namespace Dizzy\Events\Core;

use Dizzy\Events\Database\Migrations;

defined('ABSPATH') || exit;

/**
 * Handles plugin installation tasks.
 *
 * @package Dizzy\Events\Core
 */
final class Installer
{
    /**
     * Run installation routines.
     */
    public static function install(): void
    {
        Migrations::run();
    }
}
