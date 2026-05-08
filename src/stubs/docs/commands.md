# Laravel DDD Starter - Commands Reference

## Installation

```bash
composer create-project laravel/laravel mi-proyecto
cd mi-proyecto
composer require laravel-ddd/starter
php artisan ddd:install
```

## Available Commands

### `ddd:install`

Installs the DDD structure interactively in your Laravel project.

**Signature:**
```bash
php artisan ddd:install [--auth=none|breeze|sanctum] [--module=none|users]
```

**Options:**
- `--auth`: Choose authentication method (none, breeze, sanctum)
- `--module`: Generate sample module (none, users)

**Example:**
```bash
php artisan ddd:install --auth=breeze --module=users
```

**What it does:**
- Creates domain directory structure
- Sets up base classes (Entity, ValueObject, Repository, Service)
- Creates routes/domains folder
- Optionally generates Users sample module
- Optionally installs authentication

---

### `ddd:make-module`

Creates a complete DDD module with full structure.

**Signature:**
```bash
php artisan ddd:make-module {name} [--force]
```

**Arguments:**
- `name`: Module name (e.g., Users, Orders, Products)

**Options:**
- `--force`: Overwrite existing module

**Example:**
```bash
php artisan ddd:make-module Users
php artisan ddd:make-module Orders --force
```

**Creates:**
- Entities/
- ValueObjects/
- Repositories/ (interface + Eloquent implementation)
- Services/
- Http/Controllers/
- Http/Requests/
- Http/Resources/
- Routes/
- Database/Migrations/
- Providers/
- Tests/Unit/
- Tests/Feature/
- Eloquent Model in app/Models/

---

### `ddd:make-entity`

Creates a DDD entity with optional model and migration.

**Signature:**
```bash
php artisan ddd:make-entity {name} {module} [--migration] [--model]
```

**Arguments:**
- `name`: Entity name (e.g., User)
- `module`: Module name where to create the entity

**Options:**
- `--migration`: Generate migration file
- `--model`: Generate Eloquent model

**Example:**
```bash
php artisan ddd:make-entity User Users
php artisan ddd:make-entity Post Posts --migration --model
```

---

### `ddd:make-service`

Creates a service class for a module.

**Signature:**
```bash
php artisan ddd:make-service {name} {module}
```

**Arguments:**
- `name`: Service name (e.g., UserService)
- `module`: Module name

**Example:**
```bash
php artisan ddd:make-service UserService Users
php artisan ddd:make-service PostService Posts
```

---

### `ddd:make-repository`

Creates repository interface and optionally Eloquent implementation.

**Signature:**
```bash
php artisan ddd:make-repository {name} {module} [--eloquent]
```

**Arguments:**
- `name`: Repository name (e.g., UserRepository)
- `module`: Module name

**Options:**
- `--eloquent`: Create Eloquent implementation

**Example:**
```bash
php artisan ddd:make-repository UserRepository Users --eloquent
```

---

### `ddd:make-value-object`

Creates a value object class.

**Signature:**
```bash
php artisan ddd:make-value-object {name} {module}
```

**Arguments:**
- `name`: Value object name (e.g., Email)
- `module`: Module name

**Example:**
```bash
php artisan ddd:make-value-object Email Users
php artisan ddd:make-value-object Price Products
```

---

### `ddd:make-controller`

Creates a thin controller for a module.

**Signature:**
```bash
php artisan ddd:make-controller {name} {module}
```

**Arguments:**
- `name`: Controller name (e.g., UserController)
- `module`: Module name

**Example:**
```bash
php artisan ddd:make-controller UserController Users
```

---

### `ddd:make-request`

Creates a form request for validation.

**Signature:**
```bash
php artisan ddd:make-request {name} {module}
```

**Arguments:**
- `name`: Request name (e.g., CreateUserRequest)
- `module`: Module name

**Example:**
```bash
php artisan ddd:make-request CreateUserRequest Users
php artisan ddd:make-request UpdateUserRequest Users
```

---

### `ddd:make-resource`

Creates an API resource.

**Signature:**
```bash
php artisan ddd:make-resource {name} {module}
```

**Arguments:**
- `name`: Resource name (e.g., UserResource)
- `module`: Module name

**Example:**
```bash
php artisan ddd:make-resource UserResource Users
php artisan ddd:make-resource PostResource Posts
```

---

### `ddd:make-routes`

Generates routes file for a module.

**Signature:**
```bash
php artisan ddd:make-routes {module} [--api]
```

**Arguments:**
- `module`: Module name

**Options:**
- `--api`: Generate API routes

**Example:**
```bash
php artisan ddd:make-routes Users
php artisan ddd:make-routes Posts --api
```

---

## Directory Structure

After `ddd:install`, your project will have:

```
app/
├── Domains/                    # Your business logic
│   ├── Base/                   # Shared classes
│   │   ├── Entity.php
│   │   ├── ValueObject.php
│   │   ├── RepositoryInterface.php
│   │   └── Service.php
│   └── [Module]/               # e.g., Users, Posts, Orders
│       ├── Entities/
│       ├── ValueObjects/
│       ├── Repositories/
│       ├── Services/
│       ├── Http/
│       │   ├── Controllers/
│       │   ├── Requests/
│       │   └── Resources/
│       ├── Routes/
│       ├── Providers/
│       ├── Database/
│       │   └── Migrations/
│       └── Tests/
├── Application/                # Use cases/Actions
├── Infrastructure/             # External implementations
│   ├── Persistence/
│   └── HTTP/
├── Support/                    # Helpers
├── Providers/
├── Http/Controllers/           # Thin controllers
└── Models/                     # Eloquent models
routes/
├── api.php
├── web.php
└── domains/                    # Module routes
tests/
├── Unit/Domains/
└── Feature/Domains/
```

## Example Workflow

### 1. Create a new module

```bash
php artisan ddd:make-module Posts
```

### 2. Add a new entity to the module

```bash
php artisan ddd:make-entity Comment Posts --migration --model
```

### 3. Create a service

```bash
php artisan ddd:make-service CommentService Posts
```

### 4. Create a controller

```bash
php artisan ddd:make-controller CommentController Posts
```

### 5. Create form requests

```bash
php artisan ddd:make-request CreateCommentRequest Posts
php artisan ddd:make-request UpdateCommentRequest Posts
```

### 6. Create API resources

```bash
php artisan ddd:make-resource CommentResource Posts
```

### 7. Generate routes

```bash
php artisan ddd:make-routes Posts
```

Then update `routes/api.php`:

```php
require app_path('Domains/Posts/Routes/Posts.php');
```

## Tips

- Always use module names in singular form when making modules (e.g., "User" not "Users")
- The DddHelper class provides useful methods for naming and path handling
- Base classes provide common functionality for all entities and services
- Repository pattern separates data access logic from business logic
- Services contain business logic and use repositories for data access
- Controllers should be thin and delegate to services
