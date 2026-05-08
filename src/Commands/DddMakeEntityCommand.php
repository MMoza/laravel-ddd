<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DddMakeEntityCommand extends Command
{
    protected $signature = 'ddd:make-entity
        {name : The entity name (e.g. User)}
        {module? : The module name (e.g. Users)}
        {--migration : Create migration file}
        {--model : Create Eloquent model}';

    protected $description = 'Create a DDD entity with optional model and migration';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $module = $this->argument('module');

        if ($module) {
            return $this->createInModule($name, Str::studly($module));
        }

        $this->error('Please specify a module: --module=Users');
        return self::FAILURE;
    }

    protected function createInModule(string $name, string $module): int
    {
        $modulePath = config('ddd.domains_path') . '/' . $module;

        if (!File::exists($modulePath)) {
            $this->error("Module {$module} does not exist. Create it first with ddd:make-module");
            return self::FAILURE;
        }

        $entityPath = $modulePath . '/Entities';
        if (!File::exists($entityPath)) {
            File::makeDirectory($entityPath, 0755, true);
        }

        $content = <<<PHP
<?php

namespace App\Domains\{$module}\Entities;

use App\Domains\Base\Entity as BaseEntity;

class {$name} extends BaseEntity
{
    protected \$table = '{$this->tableName($name)}';

    protected \$fillable = [];

    public function getId(): string
    {
        return \$this->id;
    }
}
PHP;

        $filePath = $entityPath . "/{$name}.php";
        File::put($filePath, $content);
        $this->info("Created: {$module}/Entities/{$name}.php");

        if ($this->option('model')) {
            $this->createModel($name, $module);
        }

        if ($this->option('migration')) {
            $this->createMigration($name, $module);
        }

        $this->createTest($name, $module);

        return self::SUCCESS;
    }

    protected function createModel(string $name, string $module): void
    {
        $content = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    protected \$table = '{$this->tableName($name)}';
    protected \$fillable = [];
}
PHP;

        $modelPath = app_path("Models/{$name}.php");
        if (!File::exists(dirname($modelPath))) {
            File::makeDirectory(dirname($modelPath), 0755, true);
        }
        File::put($modelPath, $content);
        $this->info("Created: app/Models/{$name}.php");
    }

    protected function createMigration(string $name, string $module): void
    {
        $timestamp = date('Y_m_d_His');
        $tableName = $this->tableName($name);

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

        $migrationPath = database_path("migrations/{$timestamp}_create_{$tableName}_table.php");
        File::put($migrationPath, $content);
        $this->info("Created: database/migrations/{$timestamp}_create_{$tableName}_table.php");
    }

    protected function createTest(string $name, string $module): void
    {
        $testPackage = config('ddd.test_package', 'phpunit');

        if ($testPackage === 'none') {
            return;
        }

        $testPath = base_path("tests/Unit/Domains/{$module}/Entities");
        if (!File::exists($testPath)) {
            File::makeDirectory($testPath, 0755, true);
        }

        if ($testPackage === 'pest') {
            $content = $this->getPestTestContent($name, $module);
        } else {
            $content = $this->getPhpUnitTestContent($name, $module);
        }

        $filePath = $testPath . "/{$name}Test.php";
        File::put($filePath, $content);
        $this->info("Created: tests/Unit/Domains/{$module}/Entities/{$name}Test.php");
    }

    protected function getPhpUnitTestContent(string $name, string $module): string
    {
        return <<<PHP
<?php

namespace Tests\Unit\Domains\\{$module}\Entities;

use Tests\TestCase;
use App\Domains\\{$module}\Entities\\{$name};

class {$name}Test extends TestCase
{
    public function test_{$name}_can_be_created(): void
    {
        \$entity = new {$name}([
            'id' => 'test-uuid',
        ]);

        \$this->assertEquals('test-uuid', \$entity->getId());
    }
}
PHP;
    }

    protected function getPestTestContent(string $name, string $module): string
    {
        return <<<PHP
<?php

use App\Domains\\{$module}\Entities\\{$name};

test('{$name} can be created', function () {
    \$entity = new {$name}([
        'id' => 'test-uuid',
    ]);

    expect(\$entity->getId())->toBe('test-uuid');
});
PHP;
    }

    protected function tableName(string $name): string
    {
        return Str::snake(Str::pluralStudly($name));
    }
}
