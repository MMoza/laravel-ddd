# Laravel DDD Best Practices

## Estructura y Organización

### 1. Nombres de Módulos

Usa nombres en **singular** cuando sea posible:
- ✅ `Users`, `Post`, `Order`
- ❌ `UserManagement`, `PostCollection`

Los plurales se generan automáticamente en las migraciones y rutas.

### 2. Estructura de Directorios

Mantén la consistencia:

```
Domains/
├── Users/                   # Singular
│   ├── Entities/            # Contiene User.php
│   ├── Services/            # Contiene UserService.php
│   ├── Repositories/        # Contiene UserRepository.php
│   └── Http/Controllers/    # Contiene UserController.php
```

## Entidades (Entities)

### ✅ Haz

```php
class User extends Entity
{
    // Lógica del dominio
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function suspend(): void
    {
        $this->status = 'suspended';
    }
}
```

### ❌ No hagas

```php
class User extends Entity
{
    // Acceso a datos directo en la entidad
    public function getOrders()
    {
        return Order::where('user_id', $this->id)->get();
    }
}
```

## Servicios (Services)

### ✅ Haz

```php
class UserService extends Service
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function createUser(array $data): User
    {
        // Validación de reglas de negocio
        if ($this->userExists($data['email'])) {
            throw new UserAlreadyExistsException();
        }

        return $this->repository->create($data);
    }

    private function userExists(string $email): bool
    {
        return $this->repository->findByEmail($email) !== null;
    }
}
```

### ❌ No hagas

```php
class UserService extends Service
{
    public function createUser(array $data): void
    {
        // Validación incompleta
        User::create($data);
        // Efecto secundario sin control
        Mail::send(new WelcomeEmail($data['email']));
    }
}
```

## Repositorios (Repositories)

### ✅ Haz

```php
interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findActive(): Collection;
}

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        $model = User::where('email', $email)->first();
        return $model ? new User($model->toArray()) : null;
    }

    public function findActive(): Collection
    {
        return User::where('status', 'active')->get()
            ->map(fn($m) => new User($m->toArray()));
    }
}
```

### ❌ No hagas

```php
class UserRepository
{
    // Mezclar consultas con lógica de negocio
    public function getPayingUsers()
    {
        return User::where('status', 'active')
            ->whereHas('subscription')
            ->with('orders')
            ->get();
    }
}
```

## Controllers

### ✅ Haz

```php
class UserController extends Controller
{
    public function store(
        StoreUserRequest $request,
        UserService $service
    ): JsonResponse {
        $user = $service->createUser($request->validated());
        return response()->json(['data' => $user], 201);
    }
}
```

### ❌ No hagas

```php
class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Validación en el controlador
        $validated = $request->validate([...]);
        
        // Lógica de negocio en el controlador
        if (User::where('email', $validated['email'])->exists()) {
            return response()->json(['error' => 'exists'], 422);
        }

        $user = User::create($validated);
        return response()->json(['data' => $user], 201);
    }
}
```

## Value Objects

### ✅ Haz

```php
class Email extends ValueObject
{
    public function __construct(protected string $value)
    {
        $this->validate();
    }

    protected function validate(): void
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException();
        }
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isSame(ValueObject $valueObject): bool
    {
        return $this->value === $valueObject->getValue();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

### ❌ No hagas

```php
// Strings simples sin validación
$email = "user@example.com";
```

## Inyección de Dependencias

### ✅ Haz

```php
class UserService extends Service
{
    public function __construct(
        protected UserRepositoryInterface $repository,
        protected EventDispatcher $dispatcher
    ) {}
}
```

### ❌ No hagas

```php
class UserService extends Service
{
    public function createUser(array $data): User
    {
        $repo = new EloquentUserRepository();
        $user = $repo->create($data);
        
        return $user;
    }
}
```

## Testing

### ✅ Haz

```php
namespace Tests\Unit\Domains\Users\Services;

use Tests\TestCase;
use App\Domains\Users\Services\UserService;

class UserServiceTest extends TestCase
{
    public function test_create_user_successfully(): void
    {
        $repository = $this->mock(UserRepositoryInterface::class);
        $repository->expects('create')->with(['email' => 'test@example.com']);

        $service = new UserService($repository);
        $service->createUser(['email' => 'test@example.com']);
    }

    public function test_prevent_duplicate_email(): void
    {
        $this->expectException(UserAlreadyExistsException::class);
        // ...
    }
}
```

## Migraciones

### ✅ Haz

```php
// database/migrations/2024_01_01_create_users_table.php

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('name');
            $table->enum('status', ['active', 'suspended']);
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
```

## Eventos de Dominio

Para proyectos complejos, considera usar eventos:

```php
class UserCreatedEvent
{
    public function __construct(public User $user) {}
}

// En el servicio
public function createUser(array $data): User
{
    $user = $this->repository->create($data);
    event(new UserCreatedEvent($user));
    return $user;
}

// En un listener
public function handle(UserCreatedEvent $event): void
{
    // Enviar welcome email
    Mail::send(new WelcomeEmail($event->user));
}
```

## Ciclo de Vida Típico

1. **Request** → Form Request valida datos
2. **Controller** → Delega a Service
3. **Service** → Orquesta lógica de negocio
4. **Repository** → Accede a datos
5. **Entity** → Contiene estado del dominio
6. **Response** → JsonResource formatea respuesta

```
Request → FormRequest → Controller → Service → Repository → Entity → JsonResource → Response
```

## Patrón de Errores

```php
// En el dominio, lanza excepciones específicas
class UserService extends Service
{
    public function createUser(array $data): User
    {
        if ($this->userExists($data['email'])) {
            throw new UserAlreadyExistsException();
        }

        return $this->repository->create($data);
    }
}

// En el controlador, captura y maneja
class UserController extends Controller
{
    public function store(
        StoreUserRequest $request,
        UserService $service
    ): JsonResponse {
        try {
            $user = $service->createUser($request->validated());
            return response()->json(['data' => $user], 201);
        } catch (UserAlreadyExistsException $e) {
            return response()->json(['error' => 'Email already registered'], 422);
        }
    }
}
```

## Configuración del Proyecto

### autoload en composer.json

Asegúrate de que tu namespace está configurado correctamente:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    }
}
```

Luego ejecuta:

```bash
composer dump-autoload
```
