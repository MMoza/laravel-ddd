# Laravel DDD Starter Kit

A powerful and flexible Composer package that transforms a fresh Laravel 13/12 project into a fully structured Domain-Driven Design (DDD) architecture.

## Features

✅ **Complete DDD Structure** - Organized by domains/modules  
✅ **Base Classes** - Entity, ValueObject, Repository, Service  
✅ **10+ Artisan Commands** - Generate modules, entities, services, and more  
✅ **Interactive Installation** - Choose auth method (Breeze, Sanctum, or none)  
✅ **Sample Module** - Optional Users module with full examples  
✅ **API Ready** - Routes organized by domain  
✅ **Testing Structure** - Tests organized per module  
✅ **Laravel 13 & 12 Support** - Works with both versions  
✅ **PHP 8.2+** - Modern PHP requirements  

## Installation

### 1. Create a new Laravel project

```bash
composer create-project laravel/laravel my-project
cd my-project
```

### 2. Require the DDD Starter package

```bash
composer require laravel-ddd/starter
```

### 3. Run the interactive installer

```bash
php artisan ddd:install
```

You'll be asked:
- **Authentication**: None / Breeze / Sanctum
- **Sample Module**: None / Users (recommended)

That's it! Your project is now DDD-structured.

## Quick Start

### Create a new module

```bash
php artisan ddd:make-module Products
```

This creates:
- Entities
- Repositories (interface + Eloquent implementation)
- Services
- Controllers
- Routes
- Migrations
- Tests

### Generate individual components

```bash
# Create an entity
php artisan ddd:make-entity Product Products --migration --model

# Create a service
php artisan ddd:make-service ProductService Products

# Create a controller
php artisan ddd:make-controller ProductController Products

# Create form requests
php artisan ddd:make-request StoreProductRequest Products
php artisan ddd:make-request UpdateProductRequest Products

# Create API resources
php artisan ddd:make-resource ProductResource Products

# Create value objects
php artisan ddd:make-value-object Price Products
```

## Project Structure

After installation, your project will look like:

```
app/
├── Domains/                      # Your business logic organized by domain
│   ├── Base/                     # Shared base classes
│   │   ├── Entity.php            # Base entity class
│   │   ├── ValueObject.php       # Base value object class
│   │   ├── RepositoryInterface.php
│   │   └── Service.php           # Base service class
│   │
│   └── [Module]/                 # e.g., Users, Products, Orders
│       ├── Entities/
│       ├── ValueObjects/
│       ├── Repositories/
│       │   ├── ProductRepositoryInterface.php
│       │   └── EloquentProductRepository.php
│       ├── Services/
│       │   └── ProductService.php
│       ├── Http/
│       │   ├── Controllers/
│       │   │   └── ProductController.php
│       │   ├── Requests/
│       │   │   ├── StoreProductRequest.php
│       │   │   └── UpdateProductRequest.php
│       │   └── Resources/
│       │       └── ProductResource.php
│       ├── Routes/
│       │   └── Products.php
│       ├── Providers/
│       │   └── ProductsServiceProvider.php
│       ├── Database/
│       │   └── Migrations/
│       └── Tests/
│           ├── Unit/
│           │   ├── Entities/
│           │   └── Services/
│           └── Feature/
│
├── Application/                  # Use cases and actions
├── Infrastructure/               # External implementations
│   ├── Persistence/              # Eloquent repositories
│   └── HTTP/                     # HTTP adapters
├── Support/                      # Helpers and utilities
├── Http/Controllers/             # Thin controllers
└── Models/                       # Eloquent models (no business logic)

routes/
├── api.php
├── web.php
└── domains/                      # Domain-specific routes

tests/
├── Unit/Domains/
└── Feature/Domains/

database/
├── migrations/
├── factories/
└── seeders/
```

## Available Commands

| Command | Description |
|---------|-------------|
| `ddd:install` | Install DDD structure interactively |
| `ddd:make-module` | Create a complete module |
| `ddd:make-entity` | Create an entity + model + migration |
| `ddd:make-service` | Create a service class |
| `ddd:make-repository` | Create repository interface + implementation |
| `ddd:make-value-object` | Create a value object |
| `ddd:make-controller` | Create a thin controller |
| `ddd:make-request` | Create a form request |
| `ddd:make-resource` | Create an API resource |
| `ddd:make-routes` | Generate routes for a module |

For detailed documentation, see [docs/commands.md](docs/commands.md)

## Example: Building a Blog

```bash
# 1. Create the Posts module
php artisan ddd:make-module Posts

# 2. Create additional entity for comments
php artisan ddd:make-entity Comment Posts --migration --model

# 3. Create form requests
php artisan ddd:make-request StorePostRequest Posts
php artisan ddd:make-request UpdatePostRequest Posts

# 4. Create API resources
php artisan ddd:make-resource PostResource Posts
php artisan ddd:make-resource CommentResource Posts

# 5. Update routes/api.php
# Add: require app_path('Domains/Posts/Routes/Posts.php');

# 6. Run migrations
php artisan migrate
```

## Architecture Principles

### Entity
Base class for domain entities. Contains business logic related to the entity itself.

```php
class User extends Entity
{
    public function getId(): string
    {
        return $this->id;
    }
}
```

### ValueObject
Immutable objects representing values in your domain.

```php
class Email extends ValueObject
{
    public function __construct(protected string $value) {}
    
    public function getValue(): mixed { return $this->value; }
    public function isSame(ValueObject $vo): bool { ... }
    public function __toString(): string { ... }
}
```

### Repository
Abstraction layer between domain and data persistence.

```php
interface UserRepositoryInterface extends RepositoryInterface {}
class EloquentUserRepository implements UserRepositoryInterface {}
```

### Service
Orchestrates business operations using repositories.

```php
class UserService extends Service
{
    public function __construct(protected UserRepositoryInterface $repository) {}
}
```

### Controller
Thin controller that delegates to services.

```php
class UserController extends Controller
{
    public function store(Request $request, UserService $service)
    {
        $user = $service->create($request->validated());
        return response()->json(['data' => $user], 201);
    }
}
```

## Configuration

The package publishes a config file at `config/ddd.php`:

```php
return [
    'domains_path' => app_path('Domains'),
    'application_path' => app_path('Application'),
    'infrastructure_path' => app_path('Infrastructure'),
    'support_path' => app_path('Support'),
    'providers_path' => app_path('Providers'),
    'default_namespace' => 'App\\Domains',
    'generate_tests' => true,
    'routes_path' => base_path('routes/domains'),
];
```

## Compatibility

- **Laravel**: 13.x, 12.x
- **PHP**: 8.3, 8.2
- **License**: MIT

## Support

For issues, questions, or suggestions:
- GitHub Issues: [laravel-ddd/starter](https://github.com/laravel-ddd/starter/issues)
- Documentation: [docs/commands.md](docs/commands.md)

## License

The Laravel DDD Starter kit is open-sourced software licensed under the MIT license.
