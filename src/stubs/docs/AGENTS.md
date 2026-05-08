# Project Context for AI Agents

## Structure
This project uses Domain-Driven Design (DDD) with Laravel.

## Available Commands
- `ddd:make-entity <name> <module>` - Create entity with test
- `ddd:make-service <name> <module>` - Create service with test
- `ddd:make-repository <name> <module>` - Create repository with test
- `ddd:make-module <name>` - Create complete module
- `ddd:list` - List all modules
- `ddd:make-value-object <name> <module>` - Create value object
- `ddd:make-controller <name> <module>` - Create thin controller
- `ddd:make-request <name> <module>` - Create form request
- `ddd:make-resource <name> <module>` - Create API resource
- `ddd:make-routes <module>` - Generate routes for a module

## DDD Patterns
- **Entities**: `app/Domains/{Module}/Entities/`
- **Services**: `app/Domains/{Module}/Services/`
- **Repositories**: `app/Domains/{Module}/Repositories/`
- **Controllers**: `app/Domains/{Module}/Http/Controllers/`
- **Requests**: `app/Domains/{Module}/Http/Requests/`
- **Resources**: `app/Domains/{Module}/Http/Resources/`
- **Value Objects**: `app/Domains/{Module}/ValueObjects/`
- **Tests**: `tests/Unit/Domains/{Module}/`

## Standard Flow
Request → FormRequest → Controller → Service → Repository → Entity → Resource

## Key Conventions
- Use singular for entities (User, not Users)
- Controllers should be thin (delegate to services)
- Business logic in Services, not Controllers
- Repositories abstract data access
- Use `ddd:make-*` commands to maintain consistency
- Migrations are in `database/migrations/`
- Eloquent models are in `app/Models/` (no business logic)

## Naming Examples
- Entity: `User` → `app/Domains/Users/Entities/User.php`
- Service: `UserService` → `app/Domains/Users/Services/UserService.php`
- Repository: `UserRepository` → `app/Domains/Users/Repositories/UserRepository.php`
- Controller: `UserController` → `app/Domains/Users/Http/Controllers/UserController.php`
