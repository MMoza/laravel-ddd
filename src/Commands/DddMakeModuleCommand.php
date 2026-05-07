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

    public function handle(): int
    {
        $this->moduleName = Str::studly($this->argument('name'));
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

class {$this->moduleName} extends BaseEntity
{
    protected \$table = '{$this->tableName()}';

    protected \$fillable = [];

    public function getId(): string
    {
        return \$this->id;
    }
}
PHP;

        $this->createFile("Entities/{$this->moduleName}.php", $content);
    }

    protected function createModel(): void
    {
        $content = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$this->moduleName} extends Model
{
    protected \$table = '{$this->tableName()}';
    protected \$fillable = [];
    protected \$hidden = ['created_at', 'updated_at'];
}
PHP;

        $path = app_path("Models/{$this->moduleName}.php");
        if (!File::exists(dirname($path))) {
            File::makeDirectory(dirname($path), 0755, true);
        }
        File::put($path, $content);
        $this->line("Created: app/Models/{$this->moduleName}.php");
    }

    protected function createRepositoryInterface(): void
    {
        $content = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Repositories;

use App\Domains\Base\RepositoryInterface;
use App\Domains\{$this->moduleName}\Entities\{$this->moduleName};

interface {$this->moduleName}RepositoryInterface extends RepositoryInterface
{
}
PHP;

        $this->createFile("Repositories/{$this->moduleName}RepositoryInterface.php", $content);

        $content = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Repositories;

use App\Models\{$this->moduleName} as Model;
use App\Domains\{$this->moduleName}\Entities\{$this->moduleName} as Entity;

class {$this->moduleName}Repository implements {$this->moduleName}RepositoryInterface
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

        $this->createFile("Repositories/Eloquent{$this->moduleName}Repository.php", $content);
    }

    protected function createService(): void
    {
        $content = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Services;

use App\Domains\Base\Service as BaseService;
use App\Domains\{$this->moduleName}\Repositories\{$this->moduleName}RepositoryInterface;
use Illuminate\Support\Collection;

class {$this->moduleName}Service extends BaseService
{
    public function __construct(
        protected {$this->moduleName}RepositoryInterface \$repository
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

        $this->createFile("Services/{$this->moduleName}Service.php", $content);
    }

    protected function createController(): void
    {
        $content = <<<PHP
<?php

namespace App\Domains\{$this->moduleName}\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\{$this->moduleName}\Services\{$this->moduleName}Service;
use Illuminate\Http\JsonResponse;

class {$this->moduleName}Controller extends Controller
{
    public function __construct(
        protected {$this->moduleName}Service \$service
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

        $this->createFile("Http/Controllers/{$this->moduleName}Controller.php", $content);
    }

    protected function createRoutes(): void
    {
        $content = <<<PHP
<?php

use App\Domains\{$this->moduleName}\Http\Controllers\{$this->moduleName}Controller;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::resource('{$this->routeName()}', {$this->moduleName}Controller::class);
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
use App\Domains\{$this->moduleName}\Repositories\{$this->moduleName}RepositoryInterface;
use App\Domains\{$this->moduleName}\Repositories\Eloquent{$this->moduleName}Repository;
use App\Domains\{$this->moduleName}\Services\{$this->moduleName}Service;

class {$this->moduleName}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind(
            {$this->moduleName}RepositoryInterface::class,
            Eloquent{$this->moduleName}Repository::class
        );

        \$this->app->singleton({$this->moduleName}Service::class, function (\$app) {
            return new {$this->moduleName}Service(
                \$app->make({$this->moduleName}RepositoryInterface::class)
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
use App\Domains\{$this->moduleName}\Entities\{$this->moduleName};

class {$this->moduleName}Test extends TestCase
{
    public function test_{$this->moduleName}_can_be_created(): void
    {
        \$entity = new {$this->moduleName}([
            'id' => 'test-uuid',
        ]);

        \$this->assertEquals('test-uuid', \$entity->getId());
    }
}
PHP;

        $this->createFile("Tests/Unit/Entities/{$this->moduleName}Test.php", $content);

        $content = <<<PHP
<?php

namespace Tests\Feature\Domains\{$this->moduleName};

use Tests\TestCase;

class {$this->moduleName}FeatureTest extends TestCase
{
    public function test_{$this->moduleName}_index_returns_json(): void
    {
        \$response = \$this->getJson('/api/{$this->routeName()}');
        \$response->assertStatus(200);
    }
}
PHP;

        $this->createFile("Tests/Feature/{$this->moduleName}FeatureTest.php", $content);
    }

    protected function createFile(string $relativePath, string $content): void
    {
        $path = $this->modulePath . '/' . $relativePath;
        File::put($path, $content);
        $this->line("Created: {$relativePath}");
    }

    protected function tableName(): string
    {
        return Str::snake(Str::pluralStudly($this->moduleName));
    }

    protected function routeName(): string
    {
        return Str::snake(Str::pluralStudly($this->moduleName));
    }
}
PHP;