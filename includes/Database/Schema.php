<?php

declare(strict_types=1);

namespace Dizzy\Events\Database;

defined('ABSPATH') || exit;

/**
 * Database schema definitions.
 *
 * @package Dizzy\Events\Database
 */
final class Schema
{
    /**
     * Occurrence table schema.
     */
    public static function occurrences(): string
    {
        return "
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        event_id bigint(20) unsigned NOT NULL,
        start_datetime datetime NOT NULL,
        end_datetime datetime NULL,
        all_day tinyint(1) NOT NULL DEFAULT 0,
        timezone varchar(64) NOT NULL DEFAULT 'Europe/Amsterdam',
        sort_order int(11) NOT NULL DEFAULT 0,
        status varchar(32) NOT NULL DEFAULT 'publish',
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY event_id (event_id),
        KEY start_datetime (start_datetime),
        KEY sort_order (sort_order),
        KEY status (status)
        ";
    }
}