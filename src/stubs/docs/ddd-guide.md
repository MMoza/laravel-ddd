# DDD Guide for Laravel Developers

## What is DDD?

Domain-Driven Design (DDD) is a software development approach that focuses on creating a deep understanding of the business domain and organizing code around it. Instead of structuring your application by technical layers (Controllers, Models, Views), you structure it by **business domains** (Users, Orders, Products, etc.).

DDD was popularized by Eric Evans in his 2003 book *"Domain-Driven Design: Tackling Complexity in the Heart of Software"*.

---

## DDD vs MVC: What's the Difference?

### Default Laravel Structure (MVC)

```
app/
├── Http/
│   ├── Controllers/          # All controllers mixed together
│   │   ├── UserController.php
│   │   ├── OrderController.php
│   │   └── ProductController.php
│   └── Requests/
│       ├── UserRequest.php
│       ├── OrderRequest.php
│       └── ProductRequest.php
├── Models/                   # All models mixed together
│   ├── User.php
│   ├── Order.php
│   └── Product.php
└── Services/
    ├── UserService.php
    ├── OrderService.php
    └── ProductService.php
```

**Problem:** When your project grows, you spend more time scrolling through files to find related code. Everything is organized by *technical type*, not by *business context*.

### DDD Structure (with this package)

```
app/
├── Domains/
│   ├── Users/                # Everything about Users in one place
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
│   ├── Orders/               # Everything about Orders in one place
│   │   ├── Entities/
│   │   ├── Services/
│   │   └── ...
│   └── Products/             # Everything about Products in one place
│       ├── Entities/
│       ├── Services/
│       └── ...
```

**Benefit:** All code related to a domain lives together. When you need to work on "Orders", you know exactly where to look.

---

## Comparison Table

| Aspect | MVC (Default Laravel) | DDD |
|--------|----------------------|-----|
| **Organization** | By technical type (Controllers, Models) | By business domain (Users, Orders) |
| **Business Logic** | In Controllers or Models | In Services and Entities |
| **Models** | Eloquent models handle everything | Entity (domain) + Eloquent (persistence) |
| **Validation** | In Controllers or Form Requests | Form Requests + Domain Services |
| **File Structure** | Flat | Deep, organized by domain |
| **Testing** | By type (Unit, Feature) | By domain |
| **Scalability** | Harder to scale teams | Easy to assign teams to domains |
| **Onboarding** | New devs need to learn full structure | New devs learn one domain at a time |

---

## Key DDD Concepts

### 1. Entity

An object that has a **unique identity** and a lifecycle. Two entities are different even if all their properties are the same, because they have different IDs.

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
            throw new \Exception('User is already premium');
        }
        $this->subscription_status = 'premium';
    }
}

// Two users with same data are DIFFERENT entities
$user1 = new User(['email' => 'test@example.com']);
$user2 = new User(['email' => 'test@example.com']);
$user1->id !== $user2->id; // Different identities
```

### 2. Value Object

An object that has **no identity**, only value. Two value objects with the same value are considered equal. They are **immutable** (cannot be changed after creation).

```php
class Email extends ValueObject
{
    public function __construct(protected string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email');
        }
    }

    public function getValue(): mixed { return $this->value; }
    public function isSame(ValueObject $vo): bool { return $this->value === $vo->getValue(); }
    public function __toString(): string { return $this->value; }
}

// Two emails with same value are EQUAL
$email1 = new Email('test@example.com');
$email2 = new Email('test@example.com');
$email1->equals($email2); // true
```

### 3. Repository

An abstraction layer between your domain and data persistence. It defines **what** operations you can do, not **how** they are done.

```php
// Interface defines WHAT
interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findActiveUsers(): Collection;
}

// Implementation defines HOW
class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        $model = \App\Models\User::where('email', $email)->first();
        return $model ? new User($model->toArray()) : null;
    }
}
```

**Why use repositories?**
- You can change the database without changing business logic
- Easy to mock in tests
- Keeps data access logic separate from business logic

### 4. Service

A class that orchestrates business operations. It uses repositories to access data and entities to apply business rules.

```php
class UserService extends Service
{
    public function __construct(
        protected UserRepositoryInterface $repository,
        protected EventDispatcher $dispatcher
    ) {}

    public function registerUser(array $data): User
    {
        // Business rule: check if email already exists
        if ($this->repository->findByEmail($data['email'])) {
            throw new UserAlreadyExistsException();
        }

        $user = $this->repository->create($data);
        $this->dispatcher->dispatch(new UserRegisteredEvent($user));

        return $user;
    }
}
```

### 5. Thin Controller

Controllers should be **thin**. They receive the request, validate it, delegate to a service, and return a response. No business logic.

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

## Request Flow in DDD

```
HTTP Request
    │
    ▼
Form Request (Validation)
    │
    ▼
Controller (Receives request, delegates to service)
    │
    ▼
Service (Orchestrates business logic)
    │
    ├── Repository (Accesses data)
    │       │
    │       ▼
    │   Eloquent Model (Database operations)
    │
    └── Entity (Applies business rules)
    │
    ▼
API Resource (Formats response)
    │
    ▼
HTTP Response
```

---

## When to Use DDD

### ✅ Good Fit

- **Medium to large projects** with complex business logic
- **Teams of 3+ developers** working on different features
- **Long-term projects** that will evolve over years
- **Projects with multiple bounded contexts** (e.g., e-commerce, billing, inventory)
- **APIs** that need clean separation of concerns

### ❌ Not Recommended

- **Simple CRUD apps** (e.g., blog, contact form)
- **Prototypes or MVPs** where speed is more important than structure
- **Solo projects** with simple requirements
- **Short-term projects** that won't be maintained

---

## FAQ

### Is DDD overkill for small projects?

Yes, probably. DDD shines when your project grows. For a simple blog or contact form, the default Laravel structure is perfect. DDD is an investment that pays off as complexity increases.

### Can I migrate an existing Laravel project to DDD?

Yes, but it's a gradual process. You don't need to rewrite everything at once. Start by creating a new domain for a new feature, and slowly migrate existing code as you work on it.

### Does DDD work with Laravel's built-in features?

Absolutely. DDD works alongside Laravel's Eloquent, migrations, queues, events, and more. This package keeps migrations in the standard `database/migrations/` folder and uses Eloquent models for persistence.

### What about testing?

DDD makes testing easier. Each domain has its own tests, and you can mock repositories to test business logic in isolation.

### How do I convince my team to use DDD?

Start small. Show them how a new feature would be structured with DDD. Highlight the benefits:
- Easier to find related code
- Clear separation of concerns
- Better testability
- Easier to onboard new developers

### What's the difference between Entity and Eloquent Model?

| Entity | Eloquent Model |
|--------|----------------|
| Contains business logic | Contains data access logic |
| Lives in `Domains/` | Lives in `Models/` |
| Uses Repository to persist | Directly interacts with database |
| Domain concept | Technical implementation |

### Can I use DDD with APIs?

Yes! DDD is actually ideal for APIs. Thin controllers delegate to services, which return data that API Resources format. Clean separation makes it easy to maintain and version your API.

---

## Summary

| Concept | Purpose | Location |
|---------|---------|----------|
| **Entity** | Business logic and identity | `Domains/{Module}/Entities/` |
| **Value Object** | Immutable values with validation | `Domains/{Module}/ValueObjects/` |
| **Repository** | Data access abstraction | `Domains/{Module}/Repositories/` |
| **Service** | Business orchestration | `Domains/{Module}/Services/` |
| **Controller** | Request/response handling | `Domains/{Module}/Http/Controllers/` |
| **Form Request** | Input validation | `Domains/{Module}/Http/Requests/` |
| **API Resource** | Response formatting | `Domains/{Module}/Http/Resources/` |

---

## Next Steps

1. Install the package: `composer require laravel-ddd/starter`
2. Run the installer: `php artisan ddd:install`
3. Create your first module: `php artisan ddd:make-module Products`
4. Read the [Commands Reference](commands.md) for all available commands
5. Check the [Best Practices](best-practices.md) for coding guidelines
