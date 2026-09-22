# Changelog

All notable changes to this project will be documented in this file.

This project follows Semantic Versioning.

---

## [Unreleased]

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
