<?php

declare(strict_types=1);

namespace Dizzy\Events\Enums;

defined('ABSPATH') || exit;

/**
 * Occurrence status values.
 *
 * @package Dizzy\Events\Enums
 */
enum OccurrenceStatus: string
{
    /**
     * Scheduled and visible occurrence.
     */
    case PUBLISHED = 'publish';

    /**
     * Draft occurrence.
     */
    case DRAFT = 'draft';

    /**
     * Cancelled occurrence.
     */
    case CANCELLED = 'cancelled';

    /**
     * Past occurrence.
     */
    case EXPIRED = 'expired';
}