<?php

declare(strict_types=1);

namespace Modina\Modina\Console\Commands;

use Illuminate\Console\Command;
use Modina\Modina\ModuleManifest;

class ModuleDiscoverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modina:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cached module manifest';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ModuleManifest $manifest): int
    {
        $manifest->build();

        foreach ($manifest->getModuleFiles() as $module) {
            $this->line("Discovered Module: <info>{$module}</info>");
        }

        $this->info('Module manifest generated successfully.');

        return 0;
    }
}
