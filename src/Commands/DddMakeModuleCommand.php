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
            'Database/Migrations',
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

        $content = <<<PHP
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

        $this->createFile("Entities/{$entityName}.php", $content);
    }

    protected function createModel(): void
    {
        $modelPath = app_path("Models/{$this->entityName}.php");

        if (File::exists($modelPath)) {
            $this->line("Skipped: app/Models/{$this->entityName}.php (already exists)");
            return;
        }

        $entityName = $this->entityName;
        $tableName = $this->tableName();

        $content = <<<PHP
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

        File::put($modelPath, $content);
        $this->line("Created: app/Models/{$this->entityName}.php");
    }

    protected function createRepositoryInterface(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        $content = <<<PHP
<?php

namespace App\Domains\\{$moduleName}\Repositories;

use App\Domains\Base\RepositoryInterface;
use App\Domains\\{$moduleName}\Entities\\{$entityName};

interface {$entityName}RepositoryInterface extends RepositoryInterface
{
}
PHP;

        $this->createFile("Repositories/{$entityName}RepositoryInterface.php", $content);

        $content = <<<PHP
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

        $this->createFile("Repositories/Eloquent{$entityName}Repository.php", $content);
    }

    protected function createService(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        $content = <<<PHP
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

        $this->createFile("Services/{$entityName}Service.php", $content);
    }

    protected function createController(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;

        $content = <<<PHP
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

        $this->createFile("Http/Controllers/{$entityName}Controller.php", $content);
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
        $timestamp = date('Y_m_d_His');
        $tableName = $this->tableName();

        $content = <<<PHP
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

        $this->createFile("Database/Migrations/{$timestamp}_create_{$tableName}_table.php", $content);
    }

    protected function createTests(): void
    {
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;
        $routeName = $this->routeName();

        $content = <<<PHP
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

        $this->createFile("Tests/Unit/Entities/{$entityName}Test.php", $content);

        $content = <<<PHP
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

        $this->createFile("Tests/Feature/{$entityName}FeatureTest.php", $content);
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