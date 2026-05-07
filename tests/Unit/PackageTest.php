<?php

namespace LaravelDdd\Starter\Tests\Unit;

use PHPUnit\Framework\TestCase;
use LaravelDdd\Starter\Providers\DddServiceProvider;
use LaravelDdd\Starter\Support\DddHelper;
use LaravelDdd\Starter\Commands\DddInstallCommand;
use LaravelDdd\Starter\Commands\DddMakeModuleCommand;
use LaravelDdd\Starter\Commands\DddMakeEntityCommand;
use LaravelDdd\Starter\Commands\DddMakeServiceCommand;
use LaravelDdd\Starter\Commands\DddMakeRepositoryCommand;
use LaravelDdd\Starter\Commands\DddMakeControllerCommand;
use LaravelDdd\Starter\Commands\DddMakeRequestCommand;
use LaravelDdd\Starter\Commands\DddMakeResourceCommand;
use LaravelDdd\Starter\Commands\DddMakeValueObjectCommand;
use LaravelDdd\Starter\Commands\DddMakeRoutesCommand;
use LaravelDdd\Starter\Commands\DddTestCommand;

class PackageTest extends TestCase
{
    public function test_service_provider_exists(): void
    {
        $this->assertTrue(class_exists(DddServiceProvider::class));
    }

    public function test_helper_exists(): void
    {
        $this->assertTrue(class_exists(DddHelper::class));
    }

    public function test_all_commands_exist(): void
    {
        $commands = [
            DddInstallCommand::class,
            DddMakeModuleCommand::class,
            DddMakeEntityCommand::class,
            DddMakeServiceCommand::class,
            DddMakeRepositoryCommand::class,
            DddMakeControllerCommand::class,
            DddMakeRequestCommand::class,
            DddMakeResourceCommand::class,
            DddMakeValueObjectCommand::class,
            DddMakeRoutesCommand::class,
            DddTestCommand::class,
        ];

        foreach ($commands as $command) {
            $this->assertTrue(class_exists($command), "Command {$command} should exist");
        }
    }

    public function test_helper_table_name(): void
    {
        $this->assertEquals('users', DddHelper::tableName('User'));
        $this->assertEquals('posts', DddHelper::tableName('Post'));
        $this->assertEquals('order_items', DddHelper::tableName('OrderItem'));
    }

    public function test_helper_route_name(): void
    {
        $this->assertEquals('users', DddHelper::routeName('User'));
        $this->assertEquals('posts', DddHelper::routeName('Post'));
    }

    public function test_helper_controller_name(): void
    {
        $this->assertEquals('UserController', DddHelper::controllerName('User'));
        $this->assertEquals('PostController', DddHelper::controllerName('Post'));
    }

    public function test_helper_service_name(): void
    {
        $this->assertEquals('UserService', DddHelper::serviceName('User'));
        $this->assertEquals('PostService', DddHelper::serviceName('Post'));
    }

    public function test_helper_repository_name(): void
    {
        $this->assertEquals('UserRepository', DddHelper::repositoryName('User'));
    }

    public function test_helper_repository_interface_name(): void
    {
        $this->assertEquals('UserRepositoryInterface', DddHelper::repositoryInterfaceName('User'));
    }

    public function test_helper_entity_name(): void
    {
        $this->assertEquals('User', DddHelper::entityName('User'));
        $this->assertEquals('Post', DddHelper::entityName('Post'));
    }

    public function test_module_name_to_entity_name(): void
    {
        $this->assertEquals('User', \Illuminate\Support\Str::singular('Users'));
        $this->assertEquals('Post', \Illuminate\Support\Str::singular('Posts'));
        $this->assertEquals('OrderItem', \Illuminate\Support\Str::singular('OrderItems'));
    }

    public function test_entity_name_to_table_name(): void
    {
        $this->assertEquals('users', \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly('User')));
        $this->assertEquals('posts', \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly('Post')));
    }
}