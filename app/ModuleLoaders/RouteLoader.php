<?php

declare(strict_types=1);

namespace App\ModuleLoaders;

use Illuminate\Support\Facades\Route;
use Modina\Modina\ModuleLoaderContract;

class RouteLoader implements ModuleLoaderContract
{
    /**
     * @var string
     */
    private string $path;

    /**
     * RouteLoader constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    //
    public function register(): void
    {
        // TODO: Implement register() method.
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(base_path($this->path . '/routes/web.php'));
    }
}
