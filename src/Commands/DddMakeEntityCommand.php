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

    protected function tableName(string $name): string
    {
        return Str::snake(Str::pluralStudly($name));
    }
}
PHP;