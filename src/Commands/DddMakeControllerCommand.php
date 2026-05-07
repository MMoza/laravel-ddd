<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DddMakeControllerCommand extends Command
{
    protected $signature = 'ddd:make-controller
        {name : The controller name (e.g. UserController)}
        {module : The module name (e.g. Users)}';

    protected $description = 'Create a DDD thin controller';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::studly($this->argument('module'));

        $modulePath = config('ddd.domains_path') . '/' . $module;

        if (!File::exists($modulePath)) {
            $this->error("Module {$module} does not exist. Create it first with ddd:make-module");
            return self::FAILURE;
        }

        $path = $modulePath . '/Http/Controllers';
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $serviceName = Str::replace('Controller', 'Service', $name);

        $content = <<<PHP
<?php

namespace App\Domains\{$module}\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\{$module}\Services\{$serviceName};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {$name} extends Controller
{
    public function __construct(
        protected {$serviceName} \$service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => \$this->service->getAll()]);
    }

    public function show(string \$id): JsonResponse
    {
        \$entity = \$this->service->find(\$id);
        if (!\$entity) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => \$entity]);
    }

    public function store(Request \$request): JsonResponse
    {
        \$entity = \$this->service->create(\$request->validated());
        return response()->json(['data' => \$entity], 201);
    }

    public function update(Request \$request, string \$id): JsonResponse
    {
        \$entity = \$this->service->update(\$id, \$request->validated());
        if (!\$entity) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => \$entity]);
    }

    public function destroy(string \$id): JsonResponse
    {
        \$deleted = \$this->service->delete(\$id);
        return response()->json(['success' => \$deleted]);
    }
}
PHP;

        $filePath = $path . "/{$name}.php";
        File::put($filePath, $content);
        $this->info("Created: {$module}/Http/Controllers/{$name}.php");

        return self::SUCCESS;
    }
}
