<?php
/**
 * Module Registry.
 *
 * @package DizzyEventsManager
 */

declare(strict_types=1);

namespace DizzyEvents\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Registers all plugin modules.
 */
final class Modules {

	/**
	 * Get registered modules.
	 *
	 * @return array<class-string>
	 */
	public static function all(): array {

		$modules = array(

			// Core.
			Assets::class,
			Hooks::class,

			// Admin.
			// \DizzyEvents\Admin\Admin::class,

			// Events.
			// \DizzyEvents\Events\EventManager::class,

			// Artists.
			// \DizzyEvents\Artists\ArtistManager::class,

			// Reservations.
			// \DizzyEvents\Reservations\ReservationManager::class,

			// Check-in.
			// \DizzyEvents\Checkin\CheckinManager::class,

			// Reports.
			// \DizzyEvents\Reports\ReportManager::class,

			// Posters.
			// \DizzyEvents\Posters\PosterGenerator::class,

			// Social.
			// \DizzyEvents\Social\SocialExporter::class,

		);

		/**
		 * Filters the registered modules.
		 *
		 * @since 1.0.0
		 *
		 * @param array<class-string> $modules Registered modules.
		 */
		$modules = apply_filters( 'dizzy_events_modules', $modules );

		return array_values(
			array_unique(
				array_filter(
					$modules,
					static fn ( $module ) => is_string( $module )
				)
			)
		);
	}
}