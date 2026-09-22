# Changelog

All notable changes to this project will be documented in this file.

This project follows Semantic Versioning.

---

## [Unreleased]

## [4.1.12] - 2026-09-22

### Added

- Added the Jazzcafé Dizzy logo to the shared administration header.

### Changed

- Updated the header label to `Dizzy Management • Page Name` and aligned it vertically with the logo.

## [4.1.11] - 2026-09-22

### Changed

- Standardized All Events dates to the localized `DD Month YYYY` format.

## [4.1.10] - 2026-09-22

### Changed

- Removed the collapsible interaction from All Events cards.
- Displayed event date, a bullet separator, 24-hour time, publication status and the Edit Event action in one row.

## [4.1.9] - 2026-09-22

### Fixed

- Corrected Newsletter `dbDelta` index definitions so WordPress no longer interprets `UNIQUE` and `KEY` as column names.
- Preserved all existing Newsletter records while safely creating or repairing the intended indexes.

## [4.1.8] - 2026-09-22

### Changed

- Displayed each event's date and time beside its name on the All Events page.
- Sorted upcoming events from nearest to furthest, followed by past events from newest to oldest and undated events last.

## [4.1.7] - 2026-09-22

### Fixed

- Added a browser-side fallback that keeps the Dizzy sidebar expanded on every module tab even when WordPress ignores hidden-page menu filters.
- Applied the active highlight directly to the correct visible module item after the admin menu is rendered.

## [4.1.6] - 2026-09-22

### Fixed

- Reinforced the active sidebar state immediately before WordPress renders the admin menu.
- Applied the expanded Dizzy menu and active-module state consistently to all Newsletter and other module sub-tabs.

## [4.1.5] - 2026-09-22

### Fixed

- Kept the Dizzy sidebar menu expanded while viewing horizontal sub-tabs.
- Highlighted the correct module submenu for every hidden secondary administration page.

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
