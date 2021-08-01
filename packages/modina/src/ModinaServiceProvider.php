<?php

declare(strict_types=1);

namespace Modina\Modina;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Modina\Modina\Console\Commands\ModuleDiscoverCommand;

class ModinaServiceProvider extends ServiceProvider
{
    /**
     * @var ModuleLoaderContract[]
     */
    protected array $loaders = [];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../configs/modina.php', 'modina');

        $this->app->singleton(ModinaManager::class);

        if ($this->app->runningInConsole()) {
            $cachePath = $this->app->make(ModinaManager::class)->getCachedModulesPath();
            $files = new Filesystem;
            $files->delete($cachePath);
        }

        $this->app->singleton(
            ModuleManifest::class,
            fn (Application $app) => $app->make(ModinaManager::class)->makeModuleManifest()
        );

        /** @var ModuleManifest $moduleManifest */
        $moduleManifest = $this->app->make(ModuleManifest::class);

        $moduleManifest->autoload();

        foreach ($moduleManifest->manifest as $path => $module) {
            foreach ($module['loaders'] as $loaderClass => $data) {
                $this->loaders[] = app($loaderClass, array_merge(['path' => $path, 'module' => $module], $data));
            }
        }

        foreach ($this->loaders as $loader) {
            $loader->register();
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleDiscoverCommand::class,
            ]);
        }

        foreach ($this->loaders as $loader) {
            $loader->boot();
        }
    }
}
