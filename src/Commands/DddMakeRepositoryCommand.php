<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DddMakeRepositoryCommand extends Command
{
    protected $signature = 'ddd:make-repository
        {name : The repository name (e.g. UserRepository)}
        {module : The module name (e.g. Users)}
        {--eloquent : Create Eloquent implementation}';

    protected $description = 'Create a DDD repository interface and optionally Eloquent implementation';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::studly($this->argument('module'));

        $modulePath = config('ddd.domains_path') . '/' . $module;

        if (!File::exists($modulePath)) {
            $this->error("Module {$module} does not exist. Create it first with ddd:make-module");
            return self::FAILURE;
        }

        $repoPath = $modulePath . '/Repositories';
        if (!File::exists($repoPath)) {
            File::makeDirectory($repoPath, 0755, true);
        }

        $content = <<<PHP
<?php

namespace App\Domains\{$module}\Repositories;

use App\Domains\Base\RepositoryInterface;
use App\Domains\{$module}\Entities\{$module};

interface {$name}Interface extends RepositoryInterface
{
}
PHP;

        File::put($repoPath . "/{$name}Interface.php", $content);
        $this->info("Created: {$module}/Repositories/{$name}Interface.php");

        if ($this->option('eloquent')) {
            $entityName = Str::replace('Repository', '', $name);
            $content = <<<PHP
<?php

namespace App\Domains\{$module}\Repositories;

use App\Models\{$entityName} as Model;
use App\Domains\{$module}\Entities\{$entityName} as Entity;

class {$name} implements {$name}Interface
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

            File::put($repoPath . "/{$name}.php", $content);
            $this->info("Created: {$module}/Repositories/{$name}.php");
        }

        return self::SUCCESS;
    }
}
