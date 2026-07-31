<?php

declare(strict_types=1);

namespace Dizzy\Events\Contracts;

defined('ABSPATH') || exit;

/**
 * Represents a model that can be hydrated from a database row.
 *
 * @package Dizzy\Events\Contracts
 */
interface HydratesFromRow
{
    /**
     * Create a model instance from a database row.
     *
     * @param object $row Database row returned by wpdb.
     */
    public static function fromRow(object $row): static;

    /**
     * Convert model into an array.
     *
     * Mainly used by REST responses and serialization.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array;
}