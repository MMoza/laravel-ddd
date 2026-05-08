<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DddMakeModuleCommand extends Command
{
    protected $signature = 'ddd:make-module
        {name : The module name (e.g. Users)}
        {--force : Overwrite existing files}';

    protected $description = 'Create a complete DDD module structure';

    protected string $moduleName;
    protected string $modulePath;
    protected string $entityName;

    public function handle(): int
    {
        $this->moduleName = Str::studly($this->argument('name'));
        $this->entityName = Str::singular($this->moduleName);
        $this->modulePath = config('ddd.domains_path') . '/' . $this->moduleName;

        if (File::exists($this->modulePath) && !$this->option('force')) {
            $this->error("Module {$this->moduleName} already exists. Use --force to overwrite.");
            return self::FAILURE;
        }

        $this->createModuleStructure();
        $this->createEntity();
        $this->createRepositoryInterface();
        $this->createService();
        $this->createController();
        $this->createFormRequests();
        $this->createRoutes();
        $this->createServiceProvider();
        $this->createMigration();
        $this->createModel();
        $this->createTests();

        $this->info("Module {$this->moduleName} created successfully!");
        return self::SUCCESS;
    }

    protected function createModuleStructure(): void
    {
        $directories = [
            'Entities',
            'ValueObjects',
            'Repositories',
            'Services',
            'Http/Controllers',
            'Http/Requests',
            'Http/Resources',
            'Routes',
            'Providers',
            'Tests/Unit/Entities',
            'Tests/Unit/Services',
            'Tests/Feature',
        ];

        foreach ($directories as $dir) {
            $path = $this->modulePath . '/' . $dir;
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }

        $this->line("Created module directories for {$this->moduleName}");
    }

    protected function createEntity(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;
        $tableName = $this->tableName();

        $content = $this->getEntityContent();

        $this->createFile("Entities/{$entityName}.php", $content);
    }

    protected function getEntityContent(): string
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;
        $tableName = $this->tableName();

        if ($entityName === 'User') {
            return <<<'PHP'
<?php

namespace App\Domains\Users\Entities;

use App\Domains\Base\Entity as BaseEntity;

class User extends BaseEntity
{
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }
}
PHP;
        }

        return <<<PHP
<?php

namespace App\Domains\\{$moduleName}\Entities;

use App\Domains\Base\Entity as BaseEntity;

class {$entityName} extends BaseEntity
{
    protected \$table = '{$tableName}';

    protected \$fillable = [];

    public function getId(): string
    {
        return \$this->id;
    }
}
PHP;
    }

    protected function createModel(): void
    {
        $modelPath = app_path("Models/{$this->entityName}.php");

        if (File::exists($modelPath)) {
            $this->line("Skipped: app/Models/{$this->entityName}.php (already exists)");
            return;
        }

        $content = $this->getModelContent();

        File::put($modelPath, $content);
        $this->line("Created: app/Models/{$this->entityName}.php");
    }

    protected function getModelContent(): string
    {
        $entityName = $this->entityName;
        $tableName = $this->tableName();

        if ($entityName === 'User') {
            return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
PHP;
        }

        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$entityName} extends Model
{
    protected \$table = '{$tableName}';
    protected \$fillable = [];
    protected \$hidden = ['created_at', 'updated_at'];
}
PHP;
    }

    protected function createRepositoryInterface(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        $content = $this->getRepositoryInterfaceContent();

        $this->createFile("Repositories/{$entityName}RepositoryInterface.php", $content);

        $content = $this->getRepositoryImplementationContent();

        $this->createFile("Repositories/Eloquent{$entityName}Repository.php", $content);
    }

    protected function getRepositoryInterfaceContent(): string
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        if ($entityName === 'User') {
            return <<<'PHP'
<?php

namespace App\Domains\Users\Repositories;

use App\Domains\Base\RepositoryInterface;
use App\Domains\Users\Entities\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function createWithPassword(array $data): User;
}
PHP;
        }

        return <<<PHP
<?php

namespace App\Domains\\{$moduleName}\Repositories;

use App\Domains\Base\RepositoryInterface;
use App\Domains\\{$moduleName}\Entities\\{$entityName};

interface {$entityName}RepositoryInterface extends RepositoryInterface
{
}
PHP;
    }

    protected function getRepositoryImplementationContent(): string
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        if ($entityName === 'User') {
            return <<<'PHP'
<?php

namespace App\Domains\Users\Repositories;

use App\Models\User as Model;
use App\Domains\Users\Entities\User as Entity;
use Illuminate\Support\Facades\Hash;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function find(string $id): ?Entity
    {
        $model = Model::find($id);
        return $model ? new Entity($model->toArray()) : null;
    }

    public function all(): \Illuminate\Support\Collection
    {
        return Model::all()->map(fn($m) => new Entity($m->toArray()));
    }

    public function create(array $data): Entity
    {
        $model = Model::create($data);
        return new Entity($model->toArray());
    }

    public function update(string $id, array $data): ?Entity
    {
        $model = Model::find($id);
        if (!$model) return null;

        $model->update($data);
        return new Entity($model->toArray());
    }

    public function delete(string $id): bool
    {
        return Model::find($id)?->delete() ?? false;
    }

    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Model::paginate($perPage);
    }

    public function findByEmail(string $email): ?Entity
    {
        $model = Model::where('email', $email)->first();
        return $model ? new Entity($model->toArray()) : null;
    }

    public function createWithPassword(array $data): Entity
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $model = Model::create($data);
        return new Entity($model->toArray());
    }
}
PHP;
        }

        return <<<PHP
<?php

namespace App\Domains\\{$moduleName}\Repositories;

use App\Models\\{$entityName} as Model;
use App\Domains\\{$moduleName}\Entities\\{$entityName} as Entity;

class Eloquent{$entityName}Repository implements {$entityName}RepositoryInterface
{
    public function find(string \$id): ?Entity
    {
        \$model = Model::find(\$id);
        return \$model ? new Entity(\$model->toArray()) : null;
    }

    public function all(): \Illuminate\Support\Collection
    {
        return Model::all()->map(fn(\$m) => new Entity(\$m->toArray()));
    }

    public function create(array \$data): Entity
    {
        \$model = Model::create(\$data);
        return new Entity(\$model->toArray());
    }

    public function update(string \$id, array \$data): ?Entity
    {
        \$model = Model::find(\$id);
        if (!\$model) return null;

        \$model->update(\$data);
        return new Entity(\$model->toArray());
    }

    public function delete(string \$id): bool
    {
        return Model::find(\$id)?->delete() ?? false;
    }

    public function paginate(int \$perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Model::paginate(\$perPage);
    }
}
PHP;
    }

    protected function createService(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        $content = $this->getServiceContent();

        $this->createFile("Services/{$entityName}Service.php", $content);
    }

    protected function getServiceContent(): string
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        if ($entityName === 'User') {
            return <<<'PHP'
<?php

namespace App\Domains\Users\Services;

use App\Domains\Base\Service as BaseService;
use App\Domains\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserService extends BaseService
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function find(string $id): ?mixed
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email): ?mixed
    {
        return $this->repository->findByEmail($email);
    }

    public function create(array $data): mixed
    {
        return $this->repository->createWithPassword($data);
    }

    public function update(string $id, array $data): ?mixed
    {
        return $this->repository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
PHP;
        }

        return <<<PHP
<?php

namespace App\Domains\\{$moduleName}\Services;

use App\Domains\Base\Service as BaseService;
use App\Domains\\{$moduleName}\Repositories\\{$entityName}RepositoryInterface;
use Illuminate\Support\Collection;

class {$entityName}Service extends BaseService
{
    public function __construct(
        protected {$entityName}RepositoryInterface \$repository
    ) {}

    public function getAll(): Collection
    {
        return \$this->repository->all();
    }

    public function find(string \$id): ?mixed
    {
        return \$this->repository->find(\$id);
    }

    public function create(array \$data): mixed
    {
        return \$this->repository->create(\$data);
    }

    public function update(string \$id, array \$data): ?mixed
    {
        return \$this->repository->update(\$id, \$data);
    }

    public function delete(string \$id): bool
    {
        return \$this->repository->delete(\$id);
    }
}
PHP;
    }

    protected function createController(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        $content = $this->getControllerContent();

        $this->createFile("Http/Controllers/{$entityName}Controller.php", $content);
    }

    protected function getControllerContent(): string
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        if ($entityName === 'User') {
            return <<<'PHP'
<?php

namespace App\Domains\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Users\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->service->getAll()]);
    }

    public function show(string $id): JsonResponse
    {
        $entity = $this->service->find($id);
        if (!$entity) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => $entity]);
    }

    public function store(Request $request): JsonResponse
    {
        $entity = $this->service->create($request->validated());
        return response()->json(['data' => $entity], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $entity = $this->service->update($id, $request->validated());
        if (!$entity) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => $entity]);
    }

    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->service->delete($id);
        return response()->json(['success' => $deleted]);
    }
}
PHP;
        }

        return <<<PHP
<?php

namespace App\Domains\\{$moduleName}\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\\{$moduleName}\Services\\{$entityName}Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {$entityName}Controller extends Controller
{
    public function __construct(
        protected {$entityName}Service \$service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => \$this->service->getAll()]);
    }

    public function show(string \$id): JsonResponse
    {
        \$entity = \$this->service->find(\$id);
        if (!\$entity) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => \$entity]);
    }

    public function store(Request \$request): JsonResponse
    {
        \$entity = \$this->service->create(\$request->validated());
        return response()->json(['data' => \$entity], 201);
    }

    public function update(Request \$request, string \$id): JsonResponse
    {
        \$entity = \$this->service->update(\$id, \$request->validated());
        if (!\$entity) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => \$entity]);
    }

    public function destroy(string \$id): JsonResponse
    {
        \$deleted = \$this->service->delete(\$id);
        return response()->json(['success' => \$deleted]);
    }
}
PHP;
    }

    protected function createFormRequests(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        $storeContent = <<<PHP
<?php

namespace App\Domains\\{$moduleName}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store{$entityName}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
PHP;

        $this->createFile("Http/Requests/Store{$entityName}Request.php", $storeContent);

        $updateContent = <<<PHP
<?php

namespace App\Domains\\{$moduleName}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Update{$entityName}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
PHP;

        $this->createFile("Http/Requests/Update{$entityName}Request.php", $updateContent);
    }

    protected function createRoutes(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;
        $routeName = $this->routeName();

        $content = <<<PHP
<?php

use App\Domains\\{$moduleName}\Http\Controllers\\{$entityName}Controller;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::resource('{$routeName}', {$entityName}Controller::class);
});
PHP;

        $this->createFile("Routes/{$moduleName}.php", $content);
    }

    protected function createServiceProvider(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        $content = <<<PHP
<?php

namespace App\Domains\\{$moduleName}\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\\{$moduleName}\Repositories\\{$entityName}RepositoryInterface;
use App\Domains\\{$moduleName}\Repositories\Eloquent{$entityName}Repository;
use App\Domains\\{$moduleName}\Services\\{$entityName}Service;

class {$moduleName}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind(
            {$entityName}RepositoryInterface::class,
            Eloquent{$entityName}Repository::class
        );

        \$this->app->singleton({$entityName}Service::class, function (\$app) {
            return new {$entityName}Service(
                \$app->make({$entityName}RepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/{$moduleName}.php');
    }
}
PHP;

        $this->createFile("Providers/{$moduleName}ServiceProvider.php", $content);
    }

    protected function createMigration(): void
    {
        $tableName = $this->tableName();
        $timestamp = date('Y_m_d_His');

        $existingMigrations = glob(database_path("migrations/*_create_{$tableName}_table.php"));

        if (!empty($existingMigrations)) {
            $existingFile = basename($existingMigrations[0]);
            if (!$this->confirm("Migration '{$existingFile}' already exists. Overwrite?")) {
                $this->line("Skipped: {$existingFile}");
                return;
            }
            foreach ($existingMigrations as $file) {
                File::delete($file);
            }
        }

        $content = $this->getMigrationContent();

        $migrationPath = database_path("migrations/{$timestamp}_create_{$tableName}_table.php");
        File::put($migrationPath, $content);
        $this->line("Created: database/migrations/{$timestamp}_create_{$tableName}_table.php");
    }

    protected function getMigrationContent(): string
    {
        $tableName = $this->tableName();
        $entityName = $this->entityName;

        if ($entityName === 'User') {
            return $this->getUsersMigrationContent();
        }

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;
    }

    protected function getUsersMigrationContent(): string
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
PHP;
    }

    protected function createTests(): void
    {
        $testPackage = config('ddd.test_package', 'phpunit');

        if ($testPackage === 'none') {
            return;
        }

        $moduleName = $this->moduleName;
        $entityName = $this->entityName;
        $routeName = $this->routeName();

        if ($testPackage === 'pest') {
            $unitContent = $this->getPestEntityTestContent($entityName, $moduleName);
            $featureContent = $this->getPestFeatureTestContent($entityName, $moduleName, $routeName);
        } else {
            $unitContent = $this->getPhpUnitEntityTestContent($entityName, $moduleName);
            $featureContent = $this->getPhpUnitFeatureTestContent($entityName, $moduleName, $routeName);
        }

        $this->createFile("Tests/Unit/Entities/{$entityName}Test.php", $unitContent);
        $this->createFile("Tests/Feature/{$entityName}FeatureTest.php", $featureContent);
    }

    protected function getPhpUnitEntityTestContent(string $entityName, string $moduleName): string
    {
        return <<<PHP
<?php

namespace Tests\Unit\Domains\\{$moduleName}\Entities;

use Tests\TestCase;
use App\Domains\\{$moduleName}\Entities\\{$entityName};

class {$entityName}Test extends TestCase
{
    public function test_{$entityName}_can_be_created(): void
    {
        \$entity = new {$entityName}([
            'id' => 'test-uuid',
        ]);

        \$this->assertEquals('test-uuid', \$entity->getId());
    }
}
PHP;
    }

    protected function getPhpUnitFeatureTestContent(string $entityName, string $moduleName, string $routeName): string
    {
        return <<<PHP
<?php

namespace Tests\Feature\Domains\\{$moduleName};

use Tests\TestCase;

class {$entityName}FeatureTest extends TestCase
{
    public function test_{$entityName}_index_returns_json(): void
    {
        \$response = \$this->getJson('/api/{$routeName}');
        \$response->assertStatus(200);
    }
}
PHP;
    }

    protected function getPestEntityTestContent(string $entityName, string $moduleName): string
    {
        return <<<PHP
<?php

use App\Domains\\{$moduleName}\Entities\\{$entityName};

test('{$entityName} can be created', function () {
    \$entity = new {$entityName}([
        'id' => 'test-uuid',
    ]);

    expect(\$entity->getId())->toBe('test-uuid');
});
PHP;
    }

    protected function getPestFeatureTestContent(string $entityName, string $moduleName, string $routeName): string
    {
        return <<<PHP
<?php

test('{$entityName} index returns json', function () {
    \$response = \$this->getJson('/api/{$routeName}');
    \$response->assertStatus(200);
});
PHP;
    }

    protected function createFile(string $relativePath, string $content): void
    {
        $path = $this->modulePath . '/' . $relativePath;
        File::put($path, $content);
        $this->line("Created: {$relativePath}");
    }

    protected function tableName(): string
    {
        return Str::snake(Str::pluralStudly($this->entityName));
    }

    protected function routeName(): string
    {
        return Str::snake(Str::pluralStudly($this->entityName));
    }
}