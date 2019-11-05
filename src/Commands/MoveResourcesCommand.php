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
    use SuggestsComposerUpdate;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:move {resources*} {--site=} {--service=} {--app-path=} {--force} {--no-update}';

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
        $files = collect($this->discoverFiles())->mapWithKeys(function (SplFileInfo $file) {
            return [$file->getRealPath() => $this->getNewPath($file)];
        });

        if ($this->confirmChanges($files)) {
            $this->module()->createIfNotExists();

            $files->each(function ($new, $old) {
                file_exists($new)
                    ? $this->warn("Warning: Skipping ".basename($new)." as it already exists in destination (path: ".$new.")")
                    : $this->move($old, $new);
            });

            $this->info("Done. Processed {$files->count()} files!");

            if ($this->module()->wasRecentlyCreated) {
                $this->suggestComposerUpdate();
            }

            return;
        }

        $this->error('Aborted');
    }

    /**
     * @param  \Illuminate\Support\Collection  $files
     * @return bool
     */
    protected function confirmChanges(Collection $files)
    {
        $this->comment('The following files will be moved:');

        $this->table(['Source', 'Destination'], $files->map(function ($new, $old) {
            return [$old, $new];
        }));

        return $this->option('force')
            ? true
            : $this->confirm('Do you wish to continue?');
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
        if (! file_exists($targetDir = dirname($newPath))) {
            mkdir($targetDir, 0755, true);
        }

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
