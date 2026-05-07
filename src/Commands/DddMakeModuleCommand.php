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
        $content = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Entities;

use App\Domains\Base\Entity as BaseEntity;

class {$this->entityName} extends BaseEntity
{
    protected \$table = '{$this->tableName()}';

    protected \$fillable = [];

    public function getId(): string
    {
        return \$this->id;
    }
}
PHP;

        $this->createFile("Entities/{$this->entityName}.php", $content);
    }

    protected function createModel(): void
    {
        $modelPath = app_path("Models/{$this->entityName}.php");

        if (File::exists($modelPath)) {
            $this->line("Skipped: app/Models/{$this->entityName}.php (already exists)");
            return;
        }

        $content = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$this->entityName} extends Model
{
    protected \$table = '{$this->tableName()}';
    protected \$fillable = [];
    protected \$hidden = ['created_at', 'updated_at'];
}
PHP;

        File::put($modelPath, $content);
        $this->line("Created: app/Models/{$this->entityName}.php");
    }

    protected function createRepositoryInterface(): void
    {
        $content = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Repositories;

use App\Domains\Base\RepositoryInterface;
use App\Domains\{$this->moduleName}\Entities\{$this->entityName};

interface {$this->entityName}RepositoryInterface extends RepositoryInterface
{
}
PHP;

        $this->createFile("Repositories/{$this->entityName}RepositoryInterface.php", $content);

        $content = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Repositories;

use App\Models\{$this->entityName} as Model;
use App\Domains\{$this->moduleName}\Entities\{$this->entityName} as Entity;

class Eloquent{$this->entityName}Repository implements {$this->entityName}RepositoryInterface
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

        $this->createFile("Repositories/Eloquent{$this->entityName}Repository.php", $content);
    }

    protected function createService(): void
    {
        $content = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Services;

use App\Domains\Base\Service as BaseService;
use App\Domains\{$this->moduleName}\Repositories\{$this->entityName}RepositoryInterface;
use Illuminate\Support\Collection;

class {$this->entityName}Service extends BaseService
{
    public function __construct(
        protected {$this->entityName}RepositoryInterface \$repository
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

        $this->createFile("Services/{$this->entityName}Service.php", $content);
    }

    protected function createController(): void
    {
        $content = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\{$this->moduleName}\Services\{$this->entityName}Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {$this->entityName}Controller extends Controller
{
    public function __construct(
        protected {$this->entityName}Service \$service
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

        $this->createFile("Http/Controllers/{$this->entityName}Controller.php", $content);
    }

    protected function createFormRequests(): void
    {
        $storeContent = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Store{$this->entityName}Request extends FormRequest
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

        $this->createFile("Http/Requests/Store{$this->entityName}Request.php", $storeContent);

        $updateContent = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Update{$this->entityName}Request extends FormRequest
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

        $this->createFile("Http/Requests/Update{$this->entityName}Request.php", $updateContent);
    }

    protected function createRoutes(): void
    {
        $content = <<<PHP
<?php

use App\Domains\{$this->moduleName}\Http\Controllers\{$this->entityName}Controller;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::resource('{$this->routeName()}', {$this->entityName}Controller::class);
});
PHP;

        $this->createFile("Routes/{$this->moduleName}.php", $content);
    }

    protected function createServiceProvider(): void
    {
        $content = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\{$this->moduleName}\Repositories\{$this->entityName}RepositoryInterface;
use App\Domains\{$this->moduleName}\Repositories\Eloquent{$this->entityName}Repository;
use App\Domains\{$this->moduleName}\Services\{$this->entityName}Service;

class {$this->moduleName}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind(
            {$this->entityName}RepositoryInterface::class,
            Eloquent{$this->entityName}Repository::class
        );

        \$this->app->singleton({$this->entityName}Service::class, function (\$app) {
            return new {$this->entityName}Service(
                \$app->make({$this->entityName}RepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/{$this->moduleName}.php');
    }
}
PHP;

        $this->createFile("Providers/{$this->moduleName}ServiceProvider.php", $content);
    }

    protected function createMigration(): void
    {
        $timestamp = date('Y_m_d_His');
        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$this->tableName()}', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$this->tableName()}');
    }
};
PHP;

        $this->createFile("Database/Migrations/{$timestamp}_create_{$this->tableName()}_table.php", $content);
    }

    protected function createTests(): void
    {
        $content = <<<PHP
<?php

namespace Tests\Unit\Domains\{$this->moduleName}\Entities;

use Tests\TestCase;
use App\Domains\{$this->moduleName}\Entities\{$this->entityName};

class {$this->entityName}Test extends TestCase
{
    public function test_{$this->entityName}_can_be_created(): void
    {
        \$entity = new {$this->entityName}([
            'id' => 'test-uuid',
        ]);

        \$this->assertEquals('test-uuid', \$entity->getId());
    }
}
PHP;

        $this->createFile("Tests/Unit/Entities/{$this->entityName}Test.php", $content);

        $content = <<<PHP
<?php

namespace Tests\Feature\Domains\{$this->moduleName};

use Tests\TestCase;

class {$this->entityName}FeatureTest extends TestCase
{
    public function test_{$this->entityName}_index_returns_json(): void
    {
        \$response = \$this->getJson('/api/{$this->routeName()}');
        \$response->assertStatus(200);
    }
}
PHP;

        $this->createFile("Tests/Feature/{$this->entityName}FeatureTest.php", $content);
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