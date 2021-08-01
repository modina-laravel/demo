<?php

declare(strict_types=1);

namespace Modina\Modina;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Env;
use Illuminate\Support\Str;

class ModinaManager
{
    /**
     * @var Application
     */
    protected Application $app;

    /**
     * The prefixes of absolute cache paths for use during normalization.
     *
     * @var string[]
     */
    protected $absoluteCachePathPrefixes = ['/', '\\'];

    /**
     * ModinaManager constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return ModuleManifest
     */
    public function makeModuleManifest(): ModuleManifest
    {
        return new ModuleManifest(
            new Filesystem,
            $this->app->basePath(),
            $this->getBaseModuleFolder(),
            $this->getCachedModulesPath()
        );
    }

    /**
     * Get the path to the modules cache file.
     *
     * @return string
     */
    public function getCachedModulesPath(): string
    {
        return $this->normalizeCachePath('MODINA_CACHE', 'cache/modina.php');
    }

    /**
     * Normalize a relative or absolute path to a cache file.
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    protected function normalizeCachePath(string $key, string $default): string
    {
        if (is_null($env = Env::get($key))) {
            return $this->app->bootstrapPath($default);
        }

        return Str::startsWith($env, $this->absoluteCachePathPrefixes)
            ? $env
            : $this->app->basePath($env);
    }

    /**
     * @return string
     */
    protected function getBaseModuleFolder(): string
    {
        return base_path(config('modina.base-folder'));
    }
}
