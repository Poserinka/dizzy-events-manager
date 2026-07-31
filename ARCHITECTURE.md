# Architecture

Dizzy Events Manager follows a modular architecture.

```
Plugin
    │
    ▼
Autoloader
    │
    ▼
Plugin::init()
    │
    ▼
Modules
    │
    ├── Assets
    ├── Hooks
    ├── Events
    ├── Artists
    ├── Reservations
    ├── Check-in
    ├── Reports
    ├── Posters
    └── Social
```

Each module is independent and exposes a public static init() method.

The Core package is responsible only for bootstrapping.

Business logic belongs to each module.

Database installation is isolated inside Database.php.

Installer coordinates installation.

Activator and Deactivator remain thin wrappers.
