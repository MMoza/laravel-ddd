<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DddMakeResourceCommand extends Command
{
    protected $signature = 'ddd:make-resource
        {name : The resource name (e.g. UserResource)}
        {module : The module name (e.g. Users)}';

    protected $description = 'Create a DDD API resource';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::studly($this->argument('module'));

        $modulePath = config('ddd.domains_path') . '/' . $module;

        if (!File::exists($modulePath)) {
            $this->error("Module {$module} does not exist. Create it first with ddd:make-module");
            return self::FAILURE;
        }

        $path = $modulePath . '/Http/Resources';
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $content = <<<PHP
<?php

namespace App\Domains\{$module}\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {$name} extends JsonResource
{
    public function toArray(Request \$request): array
    {
        return [
            'id' => \$this->id,
            'created_at' => \$this->created_at?->toISOString(),
            'updated_at' => \$this->updated_at?->toISOString(),
        ];
    }
}
PHP;

        $filePath = $path . "/{$name}.php";
        File::put($filePath, $content);
        $this->info("Created: {$module}/Http/Resources/{$name}.php");

        return self::SUCCESS;
    }
}
PHP;