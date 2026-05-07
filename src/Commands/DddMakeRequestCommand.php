<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DddMakeRequestCommand extends Command
{
    protected $signature = 'ddd:make-request
        {name : The request name (e.g. CreateUserRequest)}
        {module : The module name (e.g. Users)}';

    protected $description = 'Create a DDD form request';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::studly($this->argument('module'));

        $modulePath = config('ddd.domains_path') . '/' . $module;

        if (!File::exists($modulePath)) {
            $this->error("Module {$module} does not exist. Create it first with ddd:make-module");
            return self::FAILURE;
        }

        $path = $modulePath . '/Http/Requests';
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $content = <<<PHP
<?php

namespace App\Domains\{$module}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$name} extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
PHP;

        $filePath = $path . "/{$name}.php";
        File::put($filePath, $content);
        $this->info("Created: {$module}/Http/Requests/{$name}.php");

        return self::SUCCESS;
    }
}
