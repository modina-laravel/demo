<?php

declare(strict_types=1);

namespace Modina\Modina;

interface ModuleLoaderContract
{
    public function register(): void;
    public function boot(): void;
}
