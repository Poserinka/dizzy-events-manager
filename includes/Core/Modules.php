<?php
/**
 * Module Registry
 *
 * Registers all plugin modules.
 *
 * @package DizzyEventsManager
 */

declare(strict_types=1);

namespace DizzyEvents\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Module registry.
 */
final class Modules {

	/**
	 * Returns all registered modules.
	 *
	 * @return array<class-string>
	 */
	public static function all(): array {

		$modules = array(

			Assets::class,

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
		 * Filters the registered plugin modules.
		 *
		 * @param array<class-string> $modules Registered modules.
		 */
		return apply_filters( 'dizzy_events_modules', $modules );
	}
}