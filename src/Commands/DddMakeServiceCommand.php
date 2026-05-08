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

        $this->createTest($name, $module);

        return self::SUCCESS;
    }

    protected function createTest(string $name, string $module): void
    {
        $testPackage = config('ddd.test_package', 'phpunit');

        if ($testPackage === 'none') {
            return;
        }

        $testPath = base_path("tests/Unit/Domains/{$module}/Services");
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
        $this->info("Created: tests/Unit/Domains/{$module}/Services/{$name}Test.php");
    }

    protected function getPhpUnitTestContent(string $name, string $module): string
    {
        $repoName = Str::replace('Service', '', $name) . 'RepositoryInterface';

        return <<<PHP
<?php

namespace Tests\Unit\Domains\\{$module}\Services;

use Tests\TestCase;
use App\Domains\\{$module}\Services\\{$name};
use App\Domains\\{$module}\Repositories\\{$repoName};

class {$name}Test extends TestCase
{
    public function test_{$name}_can_be_created(): void
    {
        \$repository = \$this->mock({$repoName}::class);
        \$service = new {$name}(\$repository);

        \$this->assertInstanceOf({$name}::class, \$service);
    }
}
PHP;
    }

    protected function getPestTestContent(string $name, string $module): string
    {
        $repoName = Str::replace('Service', '', $name) . 'RepositoryInterface';

        return <<<PHP
<?php

use App\Domains\\{$module}\Services\\{$name};
use App\Domains\\{$module}\Repositories\\{$repoName};

test('{$name} can be created', function () use ({$repoName} \$repository) {
    \$service = new {$name}(\$repository);

    expect(\$service)->toBeInstanceOf({$name}::class);
});
PHP;
    }
}
