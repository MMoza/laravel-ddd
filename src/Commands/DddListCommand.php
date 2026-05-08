<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DddListCommand extends Command
{
    protected $signature = 'ddd:list';

    protected $description = 'List all DDD modules in the project';

    public function handle(): int
    {
        $domainsPath = config('ddd.domains_path');

        if (!File::exists($domainsPath)) {
            $this->error('Domains directory does not exist. Run "php artisan ddd:install" first.');
            return self::FAILURE;
        }

        $modules = $this->getModules($domainsPath);

        if (empty($modules)) {
            $this->info('No modules found. Create one with "php artisan ddd:make-module <name>".');
            return self::SUCCESS;
        }

        $this->displayModules($modules);

        return self::SUCCESS;
    }

    protected function getModules(string $domainsPath): array
    {
        $modules = [];

        $directories = File::directories($domainsPath);

        foreach ($directories as $dir) {
            $name = basename($dir);

            if ($name === 'Base') {
                continue;
            }

            $modules[] = [
                'name' => $name,
                'entities' => $this->countFiles($dir . '/Entities'),
                'services' => $this->countFiles($dir . '/Services'),
                'repositories' => $this->countFiles($dir . '/Repositories'),
                'controllers' => $this->countFiles($dir . '/Http/Controllers'),
                'requests' => $this->countFiles($dir . '/Http/Requests'),
                'resources' => $this->countFiles($dir . '/Http/Resources'),
            ];
        }

        return $modules;
    }

    protected function countFiles(string $path): int
    {
        if (!File::exists($path)) {
            return 0;
        }

        return count(File::files($path));
    }

    protected function displayModules(array $modules): void
    {
        $rows = [];

        foreach ($modules as $module) {
            $rows[] = [
                $module['name'],
                $this->formatCount($module['entities']),
                $this->formatCount($module['services']),
                $this->formatCount($module['repositories']),
                $this->formatCount($module['controllers']),
                $this->formatCount($module['requests']),
                $this->formatCount($module['resources']),
            ];
        }

        $this->table(
            ['Module', 'Entities', 'Services', 'Repositories', 'Controllers', 'Requests', 'Resources'],
            $rows
        );

        $this->newLine();
        $this->info('Total: ' . count($modules) . ' module(s)');
    }

    protected function formatCount(int $count): string
    {
        return $count > 0 ? "✓ ({$count})" : '-';
    }
}
