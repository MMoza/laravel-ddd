<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DddMakeServiceCommand extends Command
{
    protected $signature = 'ddd:make-service
        {name : The service name (e.g. UserService)}
        {module : The module name (e.g. Users)}';

    protected $description = 'Create a DDD service class';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::studly($this->argument('module'));

        $modulePath = config('ddd.domains_path') . '/' . $module;

        if (!File::exists($modulePath)) {
            $this->error("Module {$module} does not exist. Create it first with ddd:make-module");
            return self::FAILURE;
        }

        $servicePath = $modulePath . '/Services';
        if (!File::exists($servicePath)) {
            File::makeDirectory($servicePath, 0755, true);
        }

        $repoInterface = Str::replace('Service', '', $name) . 'RepositoryInterface';

        $content = <<<PHP
<?php

namespace App\Domains\{$module}\Services;

use App\Domains\Base\Service as BaseService;
use App\Domains\{$module}\Repositories\{$repoInterface};
use Illuminate\Support\Collection;

class {$name} extends BaseService
{
    public function __construct(
        protected {$repoInterface} \$repository
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

        $filePath = $servicePath . "/{$name}.php";
        File::put($filePath, $content);
        $this->info("Created: {$module}/Services/{$name}.php");

        return self::SUCCESS;
    }
}
PHP;