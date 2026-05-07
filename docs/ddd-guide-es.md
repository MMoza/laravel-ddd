# Guía DDD para Desarrolladores Laravel

## ¿Qué es DDD?

Domain-Driven Design (DDD) es un enfoque de desarrollo de software que se centra en crear una comprensión profunda del dominio del negocio y organizar el código en torno a él. En lugar de estructurar tu aplicación por capas técnicas (Controladores, Modelos, Vistas), la estructuras por **dominios de negocio** (Usuarios, Pedidos, Productos, etc.).

DDD fue popularizado por Eric Evans en su libro de 2003 *"Domain-Driven Design: Tackling Complexity in the Heart of Software"*.

---

## DDD vs MVC: ¿Cuál es la diferencia?

### Estructura por defecto de Laravel (MVC)

```
app/
├── Http/
│   ├── Controllers/          # Todos los controladores mezclados
│   │   ├── UserController.php
│   │   ├── OrderController.php
│   │   └── ProductController.php
│   └── Requests/
│       ├── UserRequest.php
│       ├── OrderRequest.php
│       └── ProductRequest.php
├── Models/                   # Todos los modelos mezclados
│   ├── User.php
│   ├── Order.php
│   └── Product.php
└── Services/
    ├── UserService.php
    ├── OrderService.php
    └── ProductService.php
```

**Problema:** Cuando tu proyecto crece, pasas más tiempo buscando archivos relacionados. Todo está organizado por *tipo técnico*, no por *contexto de negocio*.

### Estructura DDD (con este paquete)

```
app/
├── Domains/
│   ├── Users/                # Todo sobre Usuarios en un solo lugar
│   │   ├── Entities/
│   │   │   └── User.php
│   │   ├── Services/
│   │   │   └── UserService.php
│   │   ├── Repositories/
│   │   │   ├── UserRepositoryInterface.php
│   │   │   └── EloquentUserRepository.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── UserController.php
│   │   │   ├── Requests/
│   │   │   │   └── StoreUserRequest.php
│   │   │   └── Resources/
│   │   │       └── UserResource.php
│   │   └── Tests/
│   ├── Orders/               # Todo sobre Pedidos en un solo lugar
│   │   ├── Entities/
│   │   ├── Services/
│   │   └── ...
│   └── Products/             # Todo sobre Productos en un solo lugar
│       ├── Entities/
│       ├── Services/
│       └── ...
```

**Beneficio:** Todo el código relacionado con un dominio vive junto. Cuando necesitas trabajar en "Pedidos", sabes exactamente dónde buscar.

---

## Tabla Comparativa

| Aspecto | MVC (Laravel por defecto) | DDD |
|---------|---------------------------|-----|
| **Organización** | Por tipo técnico (Controladores, Modelos) | Por dominio de negocio (Usuarios, Pedidos) |
| **Lógica de negocio** | En Controladores o Modelos | En Servicios y Entidades |
| **Modelos** | Los modelos Eloquent hacen todo | Entidad (dominio) + Eloquent (persistencia) |
| **Validación** | En Controladores o Form Requests | Form Requests + Servicios de Dominio |
| **Estructura de archivos** | Plana | Profunda, organizada por dominio |
| **Testing** | Por tipo (Unit, Feature) | Por dominio |
| **Escalabilidad** | Difícil escalar equipos | Fácil asignar equipos a dominios |
| **Onboarding** | Nuevos devs deben aprender toda la estructura | Nuevos devs aprenden un dominio a la vez |

---

## Conceptos Clave de DDD

### 1. Entidad (Entity)

Un objeto que tiene una **identidad única** y un ciclo de vida. Dos entidades son diferentes incluso si todas sus propiedades son iguales, porque tienen IDs diferentes.

```php
class User extends Entity
{
    public function isPremium(): bool
    {
        return $this->subscription_status === 'premium';
    }

    public function upgradeToPremium(): void
    {
        if ($this->isPremium()) {
            throw new \Exception('El usuario ya es premium');
        }
        $this->subscription_status = 'premium';
    }
}

// Dos usuarios con los mismos datos son entidades DIFERENTES
$user1 = new User(['email' => 'test@example.com']);
$user2 = new User(['email' => 'test@example.com']);
$user1->id !== $user2->id; // Identidades diferentes
```

### 2. Objeto de Valor (Value Object)

Un objeto que **no tiene identidad**, solo valor. Dos objetos de valor con el mismo valor se consideran iguales. Son **inmutables** (no se pueden cambiar después de su creación).

```php
class Email extends ValueObject
{
    public function __construct(protected string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido');
        }
    }

    public function getValue(): mixed { return $this->value; }
    public function isSame(ValueObject $vo): bool { return $this->value === $vo->getValue(); }
    public function __toString(): string { return $this->value; }
}

// Dos emails con el mismo valor son IGUALES
$email1 = new Email('test@example.com');
$email2 = new Email('test@example.com');
$email1->equals($email2); // true
```

### 3. Repositorio (Repository)

Una capa de abstracción entre tu dominio y la persistencia de datos. Define **qué** operaciones puedes hacer, no **cómo** se hacen.

```php
// La interfaz define QUÉ
interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findActiveUsers(): Collection;
}

// La implementación define CÓMO
class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        $model = \App\Models\User::where('email', $email)->first();
        return $model ? new User($model->toArray()) : null;
    }
}
```

**¿Por qué usar repositorios?**
- Puedes cambiar la base de datos sin cambiar la lógica de negocio
- Fácil de mockear en tests
- Mantiene la lógica de acceso a datos separada de la lógica de negocio

### 4. Servicio (Service)

Una clase que orquesta operaciones de negocio. Usa repositorios para acceder a datos y entidades para aplicar reglas de negocio.

```php
class UserService extends Service
{
    public function __construct(
        protected UserRepositoryInterface $repository,
        protected EventDispatcher $dispatcher
    ) {}

    public function registerUser(array $data): User
    {
        // Regla de negocio: verificar si el email ya existe
        if ($this->repository->findByEmail($data['email'])) {
            throw new UserAlreadyExistsException();
        }

        $user = $this->repository->create($data);
        $this->dispatcher->dispatch(new UserRegisteredEvent($user));

        return $user;
    }
}
```

### 5. Controlador Delgado (Thin Controller)

Los controladores deben ser **delgados**. Reciben la petición, la validan, delegan a un servicio y devuelven una respuesta. Sin lógica de negocio.

```php
class UserController extends Controller
{
    public function store(
        StoreUserRequest $request,
        UserService $service
    ): JsonResponse {
        $user = $service->registerUser($request->validated());
        return response()->json(['data' => $user], 201);
    }
}
```

---

## Flujo de una Petición en DDD

```
Petición HTTP
    │
    ▼
Form Request (Validación)
    │
    ▼
Controlador (Recibe la petición, delega al servicio)
    │
    ▼
Servicio (Orquesta la lógica de negocio)
    │
    ├── Repositorio (Accede a datos)
    │       │
    │       ▼
    │   Modelo Eloquent (Operaciones de BD)
    │
    └── Entidad (Aplica reglas de negocio)
    │
    ▼
API Resource (Formatea la respuesta)
    │
    ▼
Respuesta HTTP
```

---

## Cuándo Usar DDD

### ✅ Buen uso

- **Proyectos medianos a grandes** con lógica de negocio compleja
- **Equipos de 3+ desarrolladores** trabajando en diferentes funcionalidades
- **Proyectos a largo plazo** que evolucionarán durante años
- **Proyectos con múltiples contextos delimitados** (ej. e-commerce, facturación, inventario)
- **APIs** que necesitan una separación limpia de responsabilidades

### ❌ No recomendado

- **Aplicaciones CRUD simples** (ej. blog, formulario de contacto)
- **Prototipos o MVPs** donde la velocidad es más importante que la estructura
- **Proyectos en solitario** con requisitos simples
- **Proyectos a corto plazo** que no se mantendrán

---

## Preguntas Frecuentes

### ¿Es DDD excesivo para proyectos pequeños?

Probablemente sí. DDD brilla cuando tu proyecto crece. Para un blog simple o un formulario de contacto, la estructura por defecto de Laravel es perfecta. DDD es una inversión que da frutos a medida que aumenta la complejidad.

### ¿Puedo migrar un proyecto Laravel existente a DDD?

Sí, pero es un proceso gradual. No necesitas reescribir todo de una vez. Comienza creando un nuevo dominio para una nueva funcionalidad, y migra lentamente el código existente a medida que trabajes en él.

### ¿DDD funciona con las funcionalidades integradas de Laravel?

¡Absolutamente! DDD funciona junto con Eloquent, migraciones, colas, eventos y más de Laravel. Este paquete mantiene las migraciones en la carpeta estándar `database/migrations/` y usa modelos Eloquent para la persistencia.

### ¿Qué pasa con los tests?

DDD facilita los tests. Cada dominio tiene sus propios tests, y puedes mockear repositorios para probar la lógica de negocio de forma aislada.

### ¿Cómo convenzo a mi equipo de usar DDD?

Empieza pequeño. Muéstrales cómo se estructuraría una nueva funcionalidad con DDD. Destaca los beneficios:
- Más fácil encontrar código relacionado
- Separación clara de responsabilidades
- Mejor capacidad de testing
- Más fácil incorporar nuevos desarrolladores

### ¿Cuál es la diferencia entre Entidad y Modelo Eloquent?

| Entidad | Modelo Eloquent |
|---------|-----------------|
| Contiene lógica de negocio | Contiene lógica de acceso a datos |
| Vive en `Domains/` | Vive en `Models/` |
| Usa Repositorio para persistir | Interactúa directamente con la BD |
| Concepto de dominio | Implementación técnica |

### ¿Puedo usar DDD con APIs?

¡Sí! DDD es ideal para APIs. Los controladores delgados delegan a servicios, que devuelven datos que los API Resources formatean. La separación limpia facilita mantener y versionar tu API.

---

## Resumen

| Concepto | Propósito | Ubicación |
|----------|-----------|-----------|
| **Entidad (Entity)** | Lógica de negocio e identidad | `Domains/{Module}/Entities/` |
| **Objeto de Valor** | Valores inmutables con validación | `Domains/{Module}/ValueObjects/` |
| **Repositorio** | Abstracción de acceso a datos | `Domains/{Module}/Repositories/` |
| **Servicio** | Orquestación de negocio | `Domains/{Module}/Services/` |
| **Controlador** | Manejo de petición/respuesta | `Domains/{Module}/Http/Controllers/` |
| **Form Request** | Validación de entrada | `Domains/{Module}/Http/Requests/` |
| **API Resource** | Formateo de respuesta | `Domains/{Module}/Http/Resources/` |

---

## Siguientes Pasos

1. Instala el paquete: `composer require laravel-ddd/starter`
2. Ejecuta el instalador: `php artisan ddd:install`
3. Crea tu primer módulo: `php artisan ddd:make-module Products`
4. Lee la [Referencia de Comandos](commands.md) para todos los comandos disponibles
5. Consulta las [Buenas Prácticas](best-practices.md) para guías de código
