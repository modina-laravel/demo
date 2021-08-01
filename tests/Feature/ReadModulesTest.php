<?php

declare(strict_types=1);

namespace Tests\Feature;

use Modina\Modina\ModuleManifest;
use Tests\TestCase;

class ReadModulesTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        /** @var ModuleManifest $moduleManifest */
        $moduleManifest = app(ModuleManifest::class);
        dd($moduleManifest->getModuleFiles());
    }
}
