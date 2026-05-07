<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DddMakeValueObjectCommand extends Command
{
    protected $signature = 'ddd:make-value-object
        {name : The value object name (e.g. Email)}
        {module : The module name (e.g. Users)}';

    protected $description = 'Create a DDD value object class';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::studly($this->argument('module'));

        $modulePath = config('ddd.domains_path') . '/' . $module;

        if (!File::exists($modulePath)) {
            $this->error("Module {$module} does not exist. Create it first with ddd:make-module");
            return self::FAILURE;
        }

        $path = $modulePath . '/ValueObjects';
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $content = <<<PHP
<?php

namespace App\Domains\{$module}\ValueObjects;

use App\Domains\Base\ValueObject as BaseValueObject;

class {$name} extends BaseValueObject
{
    public function __construct(
        protected mixed \$value
    ) {
        \$this->validate();
    }

    protected function validate(): void
    {
        // Add validation logic here
    }

    public function getValue(): mixed
    {
        return \$this->value;
    }

    public function isSame(BaseValueObject \$valueObject): bool
    {
        return \$this->getValue() === \$valueObject->getValue();
    }

    public function __toString(): string
    {
        return (string) \$this->value;
    }
}
PHP;

        $filePath = $path . "/{$name}.php";
        File::put($filePath, $content);
        $this->info("Created: {$module}/ValueObjects/{$name}.php");

        return self::SUCCESS;
    }
}
