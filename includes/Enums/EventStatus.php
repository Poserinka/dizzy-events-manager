<?php

declare(strict_types=1);

namespace Dizzy\Events\Enums;

defined('ABSPATH') || exit;

/**
 * Event status values.
 *
 * @package Dizzy\Events\Enums
 */
enum EventStatus: string
{
    /**
     * Event is a draft.
     */
    case DRAFT = 'draft';

    /**
     * Event is publicly visible.
     */
    case PUBLISHED = 'publish';

    /**
     * Event has been cancelled.
     */
    case CANCELLED = 'cancelled';

    /**
     * Event is archived.
     */
    case ARCHIVED = 'archived';
}