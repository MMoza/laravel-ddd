# Registrando Rutas de Dominios

Una vez que crees módulos con `ddd:make-routes`, necesitas registrar las rutas en tus archivos de rutas principales.

## Opción 1: routes/api.php

Si trabajas con una API, incluye los archivos de rutas en `routes/api.php`:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Registrar rutas de dominios
require app_path('Domains/Users/Routes/Users.php');
require app_path('Domains/Posts/Routes/Posts.php');
require app_path('Domains/Orders/Routes/Orders.php');
```

## Opción 2: routes/web.php

Para rutas web tradicionales:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Registrar rutas de dominios
require app_path('Domains/Users/Routes/Users.php');
require app_path('Domains/Products/Routes/Products.php');
```

## Opción 3: Crear un Route Loader

Para proyectos grandes, puedes crear un helper que cargue automáticamente todas las rutas:

```php
<?php

// app/Support/RouteLoader.php

namespace App\Support;

use Illuminate\Support\Facades\File;

class RouteLoader
{
    public static function loadDomainRoutes(): void
    {
        $domainsPath = app_path('Domains');
        
        if (!File::exists($domainsPath)) {
            return;
        }
        
        $directories = File::directories($domainsPath);
        
        foreach ($directories as $domainPath) {
            $routesPath = $domainPath . '/Routes';
            
            if (!File::exists($routesPath)) {
                continue;
            }
            
            $routeFiles = File::glob($routesPath . '/*.php');
            
            foreach ($routeFiles as $routeFile) {
                require $routeFile;
            }
        }
    }
}
```

Luego en `routes/api.php`:

```php
<?php

use App\Support\RouteLoader;

Route::middleware('api')->group(function () {
    RouteLoader::loadDomainRoutes();
});
```

## Estructura de Rutas

Las rutas generadas por `ddd:make-routes` siguen este patrón:

```php
<?php

use App\Domains\Users\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::resource('users', UsersController::class);
});
```

Esto crea automáticamente los siguientes endpoints:

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/api/users` | index |
| POST | `/api/users` | store |
| GET | `/api/users/{user}` | show |
| PUT | `/api/users/{user}` | update |
| DELETE | `/api/users/{user}` | destroy |

## Personalizar Rutas

Después de generar las rutas, puedes editarlas según tus necesidades:

```php
<?php

use App\Domains\Users\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // Rutas custom
    Route::post('/users/email-exists', [UsersController::class, 'checkEmail']);
    Route::post('/users/verify-email', [UsersController::class, 'verifyEmail']);
    
    // Rutas RESTful estándar
    Route::resource('users', UsersController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy']);
});
```

## Parámetros Route Model Binding

Para usar Route Model Binding, asegúrate de que tu modelo está configurado:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
```

Luego puedes usar el binding en tus controladores:

```php
public function show(User $user): JsonResponse
{
    return response()->json(['data' => $user]);
}
```
