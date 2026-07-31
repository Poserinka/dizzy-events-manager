# Dizzy Events Manager

A modern, modular and professional WordPress event management plugin built for **Jazzcafé Dizzy Rotterdam**.

The plugin is designed around a clean PSR-4 architecture, modern PHP practices and WordPress Coding Standards. Although it is initially developed for Jazzcafé Dizzy, the long-term goal is to become a reusable event management framework for venues, clubs, restaurants and music organizations.

---

## Features

### Event Management

* Event Custom Post Type
* Artist management
* Venue information
* Categories & Genres
* Featured events
* Event status management
* Recurring events (planned)

### Reservations

* Reservation system
* Capacity management
* Waiting list
* Reservation approval
* Email notifications
* QR ticket generation

### Check-in

* QR code scanner
* Manual check-in
* Attendance tracking
* Live statistics

### Reports

* Attendance reports
* Reservation reports
* Capacity analysis
* CSV export

### Poster Generator

* AI-assisted poster generation
* Social media templates
* Print-ready posters

### Social Media

* Facebook export
* Instagram export
* Bluesky export
* ICS calendar export
* Google Calendar integration

---

## Architecture

```
dizzy-events-manager/
│
├── includes/
│   ├── Core/
│   ├── Events/
│   ├── Artists/
│   ├── Reservations/
│   ├── Checkin/
│   ├── Reports/
│   ├── Posters/
│   └── Social/
│
├── assets/
├── languages/
├── templates/
└── vendor/
```

---

## Requirements

* WordPress 6.5+
* PHP 8.1+
* MySQL 5.7+ / MariaDB 10.4+

---

## Installation

1. Clone the repository.

```bash
git clone https://github.com/Poserinka/dizzy-events-manager.git
```

2. Copy the plugin into the WordPress `wp-content/plugins` directory.

3. Activate the plugin from the WordPress Admin panel.

---

## Development

Recommended tools:

* PHP 8.1+
* Composer
* PHP_CodeSniffer
* WordPress Coding Standards
* PHPStan

Install development dependencies:

```bash
composer install
```

Run coding standards:

```bash
composer phpcs
```

Run static analysis:

```bash
composer phpstan
```

---

## Roadmap

* Core Framework
* Event Module
* Artist Module
* Reservation System
* QR Check-in
* Reports
* AI Poster Generator
* Social Media Export
* REST API
* Premium Extensions

---

## Coding Standards

The project follows:

* PSR-4 Autoloading
* PSR-12 Coding Style
* WordPress Coding Standards
* PHP 8.1+
* Strict Types

---

## License

Licensed under the GPL-2.0-or-later license.

---

## Author

**Poserinka Design**

GitHub:
https://github.com/Poserinka

---

## Status

🚧 Active Development

Current milestone:

**Core Framework (v0.1.0)**

