<?php

namespace LaravelDdd\Starter\Tests\Unit;

use PHPUnit\Framework\TestCase;
use LaravelDdd\Starter\Support\DddHelper;

class PackageTest extends TestCase
{
    public function test_service_provider_exists(): void
    {
        $path = __DIR__ . '/../../src/Providers/DddServiceProvider.php';
        $this->assertFileExists($path);
    }

    public function test_helper_exists(): void
    {
        $this->assertTrue(class_exists(DddHelper::class));
    }

    public function test_all_commands_exist(): void
    {
        $commands = [
            'DddInstallCommand.php',
            'DddMakeModuleCommand.php',
            'DddMakeEntityCommand.php',
            'DddMakeServiceCommand.php',
            'DddMakeRepositoryCommand.php',
            'DddMakeControllerCommand.php',
            'DddMakeRequestCommand.php',
            'DddMakeResourceCommand.php',
            'DddMakeValueObjectCommand.php',
            'DddMakeRoutesCommand.php',
            'DddTestCommand.php',
        ];

        foreach ($commands as $command) {
            $path = __DIR__ . '/../../src/Commands/' . $command;
            $this->assertFileExists($path, "Command file {$command} should exist");
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
        $this->assertEquals('User', rtrim('Users', 's'));
        $this->assertEquals('Post', rtrim('Posts', 's'));
    }

    public function test_entity_name_to_table_name(): void
    {
        $this->assertEquals('users', strtolower('Users'));
        $this->assertEquals('posts', strtolower('Posts'));
    }
}