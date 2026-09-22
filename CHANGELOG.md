# Changelog

All notable changes to this project will be documented in this file.

This project follows Semantic Versioning.

---

## [Unreleased]

## [4.2.15] - 2026-09-22

### Fixed

- Added the missing gap between the Social Templates heading and its Facebook/Instagram cards.

## [4.2.14] - 2026-09-22

### Changed

- Styled Event Categories add, list, and edit areas as Dizzy admin cards while retaining WordPress category actions.
- Kept the Events navigation active on category edit screens.

## [4.2.13] - 2026-09-22

### Changed

- Replaced the Poster Generator table with All Events-style cards showing event date, time, publication status, poster state, preview, and generator action.
- Sorted Poster Generator events in the same upcoming-first order as All Events.

## [4.2.12] - 2026-09-22

### Changed

- Matched Tickets Check-in to the other admin screens with a white heading, rounded scanner and manual check-in panels, and responsive attendance cards.

## [4.2.11] - 2026-09-22

### Changed

- Applied the shared Social Media heading, card, and table style to Poster Generator and settings tabs.
- Grouped Poster Settings controls into separate panels for images, typography, and drag-and-drop layout.
- Corrected a mismatched heading tag in Social Templates.

## [4.2.10] - 2026-09-22

### Changed

- Matched WA Notify Settings and Message Templates to the other admin pages with white headings, black text, and rounded, bordered panels.

## [4.2.9] - 2026-09-22

### Fixed

- Removed the white newsletter page background that made gaps between panels look like horizontal strips.
- Gave the newsletter heading, cards, and nested tables consistent rounded borders and subtle shadows.

## [4.2.8] - 2026-09-22

### Changed

- Added rounded report list and calendar panels to Reservation Reports and Ticket Reports.
- Moved the Schedule border, radius and shadow from the week grid to the complete calendar container.
- Added the matching card layout to Ticket Payment Settings.

## [4.2.7] - 2026-09-22

### Changed

- Applied the shared rounded card treatment to reservations, table planning, Schedule reports and settings, and ticket lists.
- Placed reservation and ticket calendars inside matching side panels.
- Restyled Newsletter pages and tables, and moved the Add Campaign action into the Newsletter heading.

## [4.2.6] - 2026-09-22

### Changed

- Applied the requested border and shadow to event editor sections.
- Added rounded cards and the refined dark hero treatment.
- Changed the event editor header to black on white.
- Added rounded Schedule headers and bordered, shadowed week grids.

## [4.2.5] - 2026-09-22

### Changed

- Standardized cards and applicable administration panels with a white background, `#E8E8EB` border and subtle `0 2px 5px #0000000d` shadow.

## [4.2.4] - 2026-09-22

### Changed

- Replaced the Schedule content heading with `Employee shift planning`.
- Replaced the Reports content heading with `Scheduled hours and shifts by employee.`.
- Applied the white content-header treatment consistently to Schedule Reports.

## [4.2.3] - 2026-09-22

### Changed

- Changed the Schedule content header to black text on white with a `#dcdcde` bottom border.

## [4.2.2] - 2026-09-22

### Fixed

- Loaded all Reservations runtime dependencies deterministically before the module boots.
- Prevented the production `EventGateway` class-not-found fatal error after plugin updates.
- Extended the suite bootstrap test to verify critical bundled Reservations classes.

## [4.2.1] - 2026-09-22

### Fixed

- Restored the shared logo and page header on Schedule and Schedule Reports pages.

### Changed

- Standardized all shared horizontal tab labels to the uppercase Schedule tab style.

## [4.2.0] - 2026-09-22

### Changed

- Introduced a shared administration layout across all Dizzy module pages.
- Added a white brand header, dark page-introduction panel, optional primary action and attached horizontal tabs.
- Added module-specific descriptions and removed duplicate native page headings from the shared layout.

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
