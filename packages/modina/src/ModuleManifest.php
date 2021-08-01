<?php

declare(strict_types=1);

namespace Modina\Modina;

use Composer\Autoload\ClassLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;

class ModuleManifest extends PackageManifest
{
    /**
     * @var string
     */
    private string $modulePath;

    /**
     * ModuleLoader constructor.
     * @param Filesystem $files
     * @param string $basePath
     * @param string $modulePath
     * @param string $manifestPath
     */
    public function  __construct(
        Filesystem $files,
        string $basePath,
        string $modulePath,
        string $manifestPath
    ) {
        $this->modulePath = $modulePath;

        parent::__construct(
            $files,
            $basePath,
            $manifestPath
        );
    }

    /**
     * @return array
     */
    public function getModulePaths(): array
    {
        $moduleFiles = array_merge(
            $this->getBaseModulePaths(),
            glob(sprintf('%s/*/*/modina.json', $this->vendorPath)) ?: []
        );

        return array_map(
            fn(string $moduleFile) => dirname($moduleFile),
            $moduleFiles
        );
    }

    /**
     * @return array
     */
    protected function getBaseModulePaths(): array
    {
        return glob(sprintf('%s/*/*/modina.json', $this->modulePath)) ?: [];
    }

    /**
     * @return array
     */
    public function getModuleFiles(): array
    {
        return array_map(
            fn (string $modulePath) => sprintf('%s/modina.json', $modulePath),
            $this->getModulePaths()
        );
    }

    public function autoload()
    {
        /** @var ClassLoader $loader */
        $loader = $this->files->getRequire(sprintf('%s/autoload.php', $this->vendorPath));

        return collect($this->getManifest())
            ->filter(fn (array $module) => isset($module['autoload']))
            ->each(function (array $module, string $key) use ($loader) {
                $autoload = $module['autoload'];

                $this->createLoader($loader, $key, $autoload);
            });
    }

    /**
     * Build the manifest and write it to disk.
     *
     * @return void
     */
    public function build()
    {
        $ignoreAll = in_array('*', $ignore = $this->modulesToIgnore());

        $this->write(
            collect($this->getModulePaths())
                ->mapWithKeys(
                    fn (string $modulePath) => [
                        $this->format($modulePath) => json_decode(
                            $this->files->get(sprintf('%s/modina.json', $modulePath)),
                            true
                        )
                    ]
                )
                ->each(function (array $module) use (&$ignore) {
                    $ignore = array_merge($ignore, $module['dont-discover'] ?? []);
                })
                ->reject(fn (array $module) => $ignoreAll || in_array($module['name'], $this->modulesToIgnore()))
                ->filter()
                ->toArray()
        );
    }

    /**
     * Format the given package name.
     *
     * @param  string  $package
     * @return string
     */
    protected function format($package)
    {
        return str_replace($this->basePath.'/', '', $package);
    }

    /**
     * Get all of the package names that should be ignored.
     *
     * @return array
     */
    protected function modulesToIgnore()
    {
        if (! is_file($this->basePath.'/composer.json')) {
            return [];
        }

        return json_decode(file_get_contents(
                $this->basePath.'/composer.json'
            ), true)['extra']['modina']['dont-discover'] ?? [];
    }

    /**
     * Registers an autoloader based on an autoload map returned by parseAutoloads
     *
     * @param  array       $autoloads see parseAutoloads return value
     * @return ClassLoader
     */
    public function createLoader(ClassLoader $loader, string $root, array $autoloads)
    {
        if (isset($autoloads['psr-0'])) {
            foreach ($autoloads['psr-0'] as $namespace => $path) {
                $loader->add($namespace, $root.'/'.$path);
            }
        }

        if (isset($autoloads['psr-4'])) {
            foreach ($autoloads['psr-4'] as $namespace => $path) {
                $loader->addPsr4($namespace, sprintf('%s/%s/%s', $this->basePath, $root, $path));
            }
        }

//        if (isset($autoloads['classmap'])) {
//            $excluded = null;
//            if (!empty($autoloads['exclude-from-classmap'])) {
//                $excluded = $autoloads['exclude-from-classmap'];
//            }
//
//            $scannedFiles = array();
//            foreach ($autoloads['classmap'] as $dir) {
//                try {
//                    $loader->addClassMap($this->generateClassMap($dir, $excluded, null, null, false, $scannedFiles));
//                } catch (\RuntimeException $e) {
//                    $this->io->writeError('<warning>'.$e->getMessage().'</warning>');
//                }
//            }
//        }

        return $loader;
    }
}
