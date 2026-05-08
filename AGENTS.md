# Laravel DDD Starter Kit - Especificación

## Objetivo
Crear un paquete Composer instalable que transforme un proyecto Laravel 13/12 en una estructura DDD.

## Compatibilidad
- Laravel: 13.x y 12.x
- PHP: 8.4+

## Paquete
- Nombre: `laravel-ddd/starter`
- Distribuidor: GitHub + Packagist
- Tipo: Paquete Composer instalable

## Flujo de Usuario
```bash
composer create-project laravel/laravel mi-proyecto
cd mi-proyecto
composer require laravel-ddd/starter
php artisan ddd:install  # Instalador interactivo
```

## Estructura DDD (app/)
```
app/
├── Domains/                    # Lógica de negocio
│   ├── Base/
│   │   ├── Entity.php
│   │   ├── ValueObject.php
│   │   ├── RepositoryInterface.php
│   │   └── Service.php
│   └── [Module]/
│       ├── Entities/
│       ├── ValueObjects/
│       ├── Repositories/
│       ├── Services/
│       ├── Tests/
│       ├── Http/Controllers/
│       ├── Http/Requests/
│       ├── Http/Resources/
│       ├── Providers/
│       ├── Routes/
│       └── Database/Migrations/
├── Application/                 # Casos de uso/Actions
├── Infrastructure/              # Implementaciones
│   ├── Persistence/
│   └── HTTP/
├── Support/                    # Helpers
├── Providers/                  # Service Providers
Http/Controllers/              # Thin controllers (delegan a Application)
Models/                        # Modelos Eloquent (sin lógica de negocio)
routes/domains/                # Rutas por módulo
tests/Unit/Domains/           # Tests por módulo
tests/Feature/Domains/
```

## Commands Artisan
| Comando | Descripción |
|---------|-------------|
| `ddd:install` | Instalador interactivo |
| `ddd:make-module <name>` | Crea módulo completo |
| `ddd:make-entity <name>` | Entidad + modelo + migration + test |
| `ddd:make-service <name>` | Crea servicio + test |
| `ddd:make-repository <name>` | Interface + Eloquent + test |
| `ddd:make-value-object <name>` | Value object |
| `ddd:make-controller <name>` | Thin controller |
| `ddd:make-request <name>` | Form request |
| `ddd:make-resource <name>` | API resource |
| `ddd:make-routes <name>` | Genera rutas del módulo |
| `ddd:list` | Lista módulos existentes |
| `ddd:test` | Ejecuta tests del proyecto |

## Instalador Interactivo
1. Authentication: None / Breeze / Sanctum
2. Sample Module: None / Users (recomendado)

## Base Classes (Domains/Base/)
- `Entity.php` - Clase base para entidades con id, timestamps
- `ValueObject.php` - Clase base para value objects inmutables
- `RepositoryInterface.php` - Interfaz base para repositorios
- `Service.php` - Clase base para servicios

## Excluir de DDD (mantener estructura Laravel)
- `database/migrations/` - Sin cambios
- `database/factories/`
- `database/seeders/`
- `routes/` (excepto domains/)
- `bootstrap/`
- `config/`
- `public/`
- `resources/`
- `storage/`
- `tests/` - Estructura global pero con subcarpetas Domains

## Testing
- Tests dentro de cada módulo en `Tests/Unit/` y `Tests/Feature/`
- También disponibles en `tests/Unit/Domains/` y `tests/Feature/Domains/`

## Documentación
- `docs/commands.md` - Referencia de comandos
- `README.md` - Uso del paquete

## Servicios a Instalar según selección
- None: Solo estructura DDD
- Breeze: `composer require laravel/breeze --dev` + configure
- Sanctum: `composer require laravel/sanctum --dev` + configure