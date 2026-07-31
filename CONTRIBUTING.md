# Contributing

Thank you for considering contributing to Dizzy Events Manager.

## Requirements

- PHP 8.1+
- WordPress 6.5+
- Composer

## Setup

```bash
composer install
```

Run coding standards

```bash
composer phpcs
```

Run PHPStan

```bash
composer phpstan
```

Before submitting a Pull Request

- All tests must pass
- PHPCS must pass
- PHPStan must pass

## Commit Messages

Use Conventional Commits.

Examples:

feat(events): add recurring events

fix(reservations): fix email validation

refactor(core): simplify bootstrap
