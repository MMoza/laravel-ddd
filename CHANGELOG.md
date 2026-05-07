# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2024-05-07

### Added
- Initial release of Laravel DDD Starter Kit
- `ddd:install` command with interactive setup
- `ddd:make-module` command for creating complete modules
- `ddd:make-entity` command for creating entities
- `ddd:make-service` command for creating services
- `ddd:make-repository` command for creating repositories
- `ddd:make-controller` command for creating controllers
- `ddd:make-request` command for creating form requests
- `ddd:make-resource` command for creating API resources
- `ddd:make-value-object` command for creating value objects
- `ddd:make-routes` command for generating routes
- Base classes: Entity, ValueObject, Repository, Service
- Support for Laravel 12 and 13
- Support for PHP 8.2 and 8.3
- Comprehensive documentation
- Best practices guide
- Stubs for code generation
- DddHelper utility class

### Features
- Interactive installer with auth options (None/Breeze/Sanctum)
- Optional Users module generation
- Complete DDD structure scaffold
- API-ready routing system
- Testing structure per module

## Future Versions

### Planned for 1.1.0
- Automatic route discovery and registration
- Additional domain event support
- Query object pattern support
- CQRS command pattern examples

### Planned for 1.2.0
- Artisan commands for common DDD operations
- Enhanced repository query builder
- Domain service factories
- Advanced validation value objects
