<?php

declare(strict_types=1);

namespace Dizzy\Events\Support;

defined('ABSPATH') || exit;

/**
 * Event default values and formatting helpers.
 */
final class EventDefaults {

	public static function venue(): string {
		return 'Jazzcafé Dizzy';
	}

	public static function address(): string {
		return "'s-Gravendijkwal 127, 3021 EK Rotterdam";
	}

	public static function mapsUrl(): string {
		return 'https://maps.app.goo.gl/t73PkgDRtb6RvKFMA';
	}

	public static function formatPrice(float $price): string {
		if ($price <= 0) {
			return '';
		}

		return '€' . number_format($price, 2, ',', '.');
	}

	/**
	 * @return string[]
	 */
	public static function timeOptions(): array {
		return array(
			'14:00', '14:30',
			'15:00', '15:30',
			'16:00', '16:30',
			'17:00', '17:30',
			'18:00', '18:30',
			'19:00', '19:30',
			'20:00', '20:30',
			'21:00', '21:30',
			'22:00', '22:30',
			'23:00', '23:30',
			'00:00'
		);
	}
}
