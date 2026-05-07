<?php

namespace LaravelDdd\Starter\Support;

use Illuminate\Support\Str;

class DddHelper
{
    public static function domainPath(string $module = ''): string
    {
        $basePath = config('ddd.domains_path');
        return $module ? $basePath . '/' . Str::studly($module) : $basePath;
    }

    public static function applicationPath(string $path = ''): string
    {
        $basePath = config('ddd.application_path');
        return $path ? $basePath . '/' . $path : $basePath;
    }

    public static function infrastructurePath(string $path = ''): string
    {
        $basePath = config('ddd.infrastructure_path');
        return $path ? $basePath . '/' . $path : $basePath;
    }

    public static function supportPath(string $path = ''): string
    {
        $basePath = config('ddd.support_path');
        return $path ? $basePath . '/' . $path : $basePath;
    }

    public static function tableName(string $name): string
    {
        return Str::snake(Str::pluralStudly($name));
    }

    public static function routeName(string $name): string
    {
        return Str::snake(Str::pluralStudly($name));
    }

    public static function controllerName(string $name): string
    {
        return Str::studly($name) . 'Controller';
    }

    public static function serviceName(string $name): string
    {
        return Str::studly($name) . 'Service';
    }

    public static function repositoryName(string $name): string
    {
        return Str::studly($name) . 'Repository';
    }

    public static function repositoryInterfaceName(string $name): string
    {
        return Str::studly($name) . 'RepositoryInterface';
    }

    public static function entityName(string $name): string
    {
        return Str::studly($name);
    }
}
