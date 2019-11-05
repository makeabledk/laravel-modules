<?php

namespace Makeable\LaravelModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Makeable\LaravelModules\Module;
use PhpParser\Node\Expr\AssignOp\Mod;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class MoveResourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:move {resources*} {--site=} {--service=} {--app-path=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move resources to a module';

    /**
     * @var Module
     */
    protected $module;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = collect($this->discoverFiles());
        $blueprint = $files->mapWithKeys(function (SplFileInfo $file) {
            return [$file->getRealPath() => $this->getNewPath($file)];
        });

        $this->comment('The following files will be moved:');

        $this->table(['Source', 'Destination'], $blueprint->map(function ($new, $old) {
            return [$old, $new];
        }));

        if (! $this->option('force')) {
            if (! $this->confirm('Do you wish to continue?')) {
                $this->error('Aborted');
                exit;
            }
        }

        $this->module()->createIfNotExists();

        $blueprint
            ->each(function ($new, $old) {
                $this->move($old, $new);
            })
            ->tap(function (Collection $files) {
                $this->info("Successfully moved {$files->count()} files!");
            });
    }

    /**
     * @return string
     */
    protected function appPath()
    {
        return $this->option('app-path') ?: app_path();
    }

    /**
     * @return string
     */
    protected function appNamespace()
    {
        return app()->getNamespace();
    }

    /**
     * @return array
     */
    protected function discoverFiles()
    {
        return iterator_to_array(
            (new Finder)
                ->in($this->appPath())
                ->name(
                    array_map(function ($search) {
                        return "{$search}*";
                    }, $this->argument('resources'))
                )
                ->filter(function (\SplFileInfo $file) {
                    return $file->getExtension() === 'php';
                })
        );
    }

    /**
     * @param  \Symfony\Component\Finder\SplFileInfo  $file
     * @return mixed
     */
    protected function getNewPath(SplFileInfo $file)
    {
        abort_unless(Str::startsWith($file->getRealPath(), $this->appPath()), 0);

        return str_replace($this->appPath(), $this->module()->getModuleAppPath(), $file->getRealPath());
    }

    /**
     * @return \Makeable\LaravelModules\Module
     */
    protected function module()
    {
        if ($this->module) {
            return $this->module;
        }

        if ($this->option('site')) {
            return $this->module = new Module('sites', $this->option('site'));
        }

        if ($this->option('service')) {
            return $this->module = new Module('services', $this->option('service'));
        }

        abort(400, 'Please specify which module to move the resources, using either --site or --service option.');
    }

    /**
     * @param $oldPath
     * @param $newPath
     */
    protected function move($oldPath, $newPath)
    {
        rename($oldPath, $newPath);

        $this->updateNamespace($newPath);
    }

    /**
     * @param $path
     */
    protected function updateNamespace($path)
    {
        $contents = file_get_contents($path);
        $contents = str_replace(
            'namespace '.trim($this->appNamespace(), '\\'),
            'namespace '.trim($this->module()->getNamespace(), '\\'),
            $contents
        );

        file_put_contents($path, $contents);
    }
}
