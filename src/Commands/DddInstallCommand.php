<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class DddInstallCommand extends Command
{
    protected $signature = 'ddd:install
        {--auth= : Authentication option (none|breeze|sanctum)}
        {--module= : Sample module (none|users)}
        {--docs= : Documentation language (en|es|both|no)}
        {--agents= : Download AGENTS.md for AI agents (yes|no)}';

    protected $description = 'Install DDD structure in Laravel project';

    public function handle(): int
    {
        $auth = $this->option('auth') ?? $this->choice(
            'Authentication',
            ['none' => 'None', 'breeze' => 'Breeze', 'sanctum' => 'Sanctum (API only)'],
            'none'
        );

        $module = $this->option('module') ?? $this->choice(
            'Sample Module',
            ['none' => 'None', 'users' => 'Users (recommended)'],
            'users'
        );

        $docs = $this->option('docs') ?? $this->choice(
            'Documentation',
            ['en' => 'English', 'es' => 'Español', 'both' => 'Both languages', 'no' => 'No thanks'],
            'en'
        );

        $agents = $this->option('agents') ?? $this->choice(
            'Download AGENTS.md for AI agents?',
            ['yes' => 'Yes (recommended)', 'no' => 'No thanks'],
            'yes'
        );

        $this->info('Installing DDD structure...');

        $this->createDirectoryStructure();
        $this->copyBaseClasses();
        $this->createDomainsFolder();

        if ($docs !== 'no') {
            $this->copyDocumentation($docs);
        }

        if ($agents === 'yes') {
            $this->copyAgentsFile();
        }

        if ($module === 'users') {
            $this->createUsersModule();
        }

        if ($auth !== 'none') {
            $this->installAuth($auth);
        }

        $this->info('DDD structure installed successfully!');
        $this->info('Run "php artisan list" to see available DDD commands.');

        return self::SUCCESS;
    }

    protected function createDirectoryStructure(): void
    {
        $basePath = app_path();

        $directories = [
            'Domains/Base',
            'Application',
            'Infrastructure/Persistence',
            'Infrastructure/HTTP',
            'Support',
            'Providers',
        ];

        foreach ($directories as $dir) {
            $path = $basePath . '/' . $dir;
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
                $this->line("Created: {$dir}");
            }
        }

        $routesDomainsPath = base_path('routes/domains');
        if (!File::exists($routesDomainsPath)) {
            File::makeDirectory($routesDomainsPath, 0755, true);
            $this->line('Created: routes/domains');
        }
    }

    protected function copyBaseClasses(): void
    {
        $basePath = app_path('Domains/Base');

        $this->createEntityBaseClass($basePath);
        $this->createValueObjectBaseClass($basePath);
        $this->createRepositoryInterface($basePath);
        $this->createServiceBaseClass($basePath);

        $this->line('Created Base classes in Domains/Base');
    }

    protected function createEntityBaseClass(string $path): void
    {
        $content = <<<'PHP'
<?php

namespace App\Domains\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

abstract class Entity extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    abstract public function getId(): string;

    public function isSameEntity(self $entity): bool
    {
        return $this->getId() === $entity->getId();
    }
}
PHP;

        File::put($path . '/Entity.php', $content);
    }

    protected function createValueObjectBaseClass(string $path): void
    {
        $content = <<<'PHP'
<?php

namespace App\Domains\Base;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

abstract class ValueObject implements Arrayable, JsonSerializable
{
    abstract public function getValue(): mixed;

    abstract public function isSame(ValueObject $valueObject): bool;

    public function equals(?ValueObject $valueObject): bool
    {
        if (is_null($valueObject)) {
            return false;
        }

        return $this->isSame($valueObject);
    }

    abstract public function __toString(): string;

    public function toArray(): array
    {
        return ['value' => $this->getValue()];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
PHP;

        File::put($path . '/ValueObject.php', $content);
    }

    protected function createRepositoryInterface(string $path): void
    {
        $content = <<<'PHP'
<?php

namespace App\Domains\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function find(string $id): ?Model;

    public function all(): Collection;

    public function create(array $data): Model;

    public function update(string $id, array $data): ?Model;

    public function delete(string $id): bool;

    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;
}
PHP;

        File::put($path . '/RepositoryInterface.php', $content);
    }

    protected function createServiceBaseClass(string $path): void
    {
        $content = <<<'PHP'
<?php

namespace App\Domains\Base;

abstract class Service
{
    protected function error(string $message, int $code = 400): void
    {
        throw new \Exception($message, $code);
    }

    protected function success(mixed $data = null, string $message = 'Operation successful'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }
}
PHP;

        File::put($path . '/Service.php', $content);
    }

    protected function createDomainsFolder(): void
    {
        $domainsPath = config('ddd.domains_path');

        if (!File::exists($domainsPath)) {
            File::makeDirectory($domainsPath, 0755, true);
        }
    }

    protected function copyDocumentation(string $lang): void
    {
        $docsPath = base_path('docs/ddd');
        if (!File::exists($docsPath)) {
            File::makeDirectory($docsPath, 0755, true);
        }

        $stubPath = __DIR__ . '/../stubs/docs';

        // Always copy English docs
        $englishDocs = [
            'ddd-guide.md',
            'commands.md',
            'best-practices.md',
            'routes.md',
        ];

        foreach ($englishDocs as $file) {
            if (File::exists($stubPath . '/' . $file)) {
                File::copy($stubPath . '/' . $file, $docsPath . '/' . $file);
            }
        }

        // Copy Spanish docs if selected
        if (in_array($lang, ['es', 'both'])) {
            if (File::exists($stubPath . '/ddd-guide-es.md')) {
                File::copy($stubPath . '/ddd-guide-es.md', $docsPath . '/ddd-guide-es.md');
            }
        }

        $this->line('Created: docs/ddd/');
    }

    protected function copyAgentsFile(): void
    {
        $docsPath = base_path('docs');
        if (!File::exists($docsPath)) {
            File::makeDirectory($docsPath, 0755, true);
        }

        $stubPath = __DIR__ . '/../stubs/docs';

        if (File::exists($stubPath . '/AGENTS.md')) {
            File::copy($stubPath . '/AGENTS.md', $docsPath . '/AGENTS.md');
        }

        $this->line('Created: docs/AGENTS.md');
    }

    protected function createUsersModule(): void
    {
        $this->call('ddd:make-module', ['name' => 'Users', '--force' => true]);
    }

    protected function installAuth(string $auth): void
    {
        $this->info("Installing {$auth}...");

        switch ($auth) {
            case 'breeze':
                $this->installBreeze();
                break;
            case 'sanctum':
                $this->installSanctum();
                break;
        }
    }

    protected function installBreeze(): void
    {
        $this->info('Installing Laravel Breeze...');
        $this->executeShellCommand(['composer', 'require', 'laravel/breeze', '--dev']);
        $this->executeShellCommand(['php', 'artisan', 'breeze:install', 'blade', '--quiet']);
    }

    protected function installSanctum(): void
    {
        $this->info('Installing Laravel Sanctum...');
        $this->executeShellCommand(['composer', 'require', 'laravel/sanctum', '--dev']);
        $this->call('vendor:publish', ['--provider' => 'Laravel\Sanctum\SanctumServiceProvider', '--force' => true]);
    }

    protected function executeShellCommand(array $command): void
    {
        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) {
            $this->line($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->error("Command failed: " . implode(' ', $command));
            $this->error($process->getErrorOutput());
        }
    }
}