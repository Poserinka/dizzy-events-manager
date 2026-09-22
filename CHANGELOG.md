# Changelog

All notable changes to this project will be documented in this file.

This project follows Semantic Versioning.

---

## [Unreleased]

## [4.1.4] - 2026-09-22

### Changed

- Split reservation card summaries into separate guest/contact and date/time/party-size lines.
- Tightened the reservation information layout to match the compact reference design.

## [4.1.3] - 2026-09-22

### Changed

- Replaced the Reservations table with a responsive card list matching the unified Dizzy administration design.
- Added an always-visible reservation summary, details panel and automatic status selector to each reservation card.

## [4.1.2] - 2026-09-22

### Changed

- Centered Dizzy administration pages in a consistent shared content column.
- Aligned horizontal navigation tabs with each page's content.
- Changed the Events overview to compact expandable event cards.

## [4.1.1] - 2026-09-22

### Fixed

- Prevented a PHP deprecation warning on hidden Dizzy tab pages by always providing WordPress with a non-null admin page title.

## [4.1.0] - 2026-09-22

### Added

- Added a unified Dizzy Management admin area with a single Dizzy sidebar menu.
- Added horizontal page tabs for Events, Reservations, Schedule, Tickets, Newsletter, WAnotify and Social Media.
- Added an event overview landing page with card-based event access.

### Changed

- Simplified the WordPress sidebar so only each module's main entry is shown.
- Kept secondary tools available through their module's horizontal tabs.

## [4.0.0] - 2026-09-22

### Added

- Bundled Newsletter, Reservations, Schedule, Social Media, Tickets and WAnotify as isolated modules.
- Added a module status page under Events → Dizzy Suite.
- Added safe detection of active standalone plugins to prevent duplicate hooks, cron jobs, payments and notifications.
- Added self-healing database migration and cron scheduling for bundled modules.

### Changed

- Moved module administration pages under the Events menu for administrators.
- Preserved the existing database tables, options, post meta, roles, REST routes, webhooks and scheduled hook names.

### Added

- Core architecture
- PSR-4 Autoloader
- Plugin Bootstrap
- Module Registry

---

## [0.1.0] - 2026-07-31

### Added

- Initial repository
- Core bootstrap
- GitHub Actions
- Composer support
- WordPress Coding Standards
