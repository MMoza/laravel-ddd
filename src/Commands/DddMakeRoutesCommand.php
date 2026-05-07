<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DddMakeRoutesCommand extends Command
{
    protected $signature = 'ddd:make-routes
        {module : The module name (e.g. Users)}
        {--api : Create API routes}';

    protected $description = 'Generate routes file for a module';

    public function handle(): int
    {
        $module = Str::studly($this->argument('module'));

        $modulePath = config('ddd.domains_path') . '/' . $module;

        if (!File::exists($modulePath)) {
            $this->error("Module {$module} does not exist. Create it first with ddd:make-module");
            return self::FAILURE;
        }

        $routesPath = $modulePath . '/Routes';
        if (!File::exists($routesPath)) {
            File::makeDirectory($routesPath, 0755, true);
        }

        $controllerName = $module . 'Controller';
        $routeName = Str::snake(Str::pluralStudly($module));

        $content = <<<PHP
<?php

use App\Domains\{$module}\Http\Controllers\{$controllerName};
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::resource('{$routeName}', {$controllerName}::class);
});
PHP;

        $filePath = $routesPath . "/{$module}.php";
        File::put($filePath, $content);
        $this->info("Created: {$module}/Routes/{$module}.php");

        $this->info('');
        $this->warn('Remember to register routes in routes/api.php:');
        $this->warn("Route::include(app_path('Domains/{$module}/Routes/{$module}.php'));");

        return self::SUCCESS;
    }
}
