<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DddInstallCommand extends Command
{
    protected $signature = 'ddd:install
        {--auth= : Authentication option (none|breeze|sanctum)}
        {--module= : Sample module (none|users)}';

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

        $this->info('Installing DDD structure...');

        $this->createDirectoryStructure();
        $this->copyBaseClasses();
        $this->createDomainsFolder();

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
        $this->warn('Run: composer require laravel/breeze --dev');
        $this->warn('Then run: php artisan breeze:install');
    }

    protected function installSanctum(): void
    {
        $this->warn('Run: composer require laravel/sanctum --dev');
        $this->warn('Then run: php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"');
    }
}
