<?php

namespace IDEHelperGenerator\Command;

use IDEHelperGenerator\Console\Command;
use IDEHelperGenerator\Console\FilesDumper;
use IDEHelperGenerator\Console\Parser;
use IDEHelperGenerator\GeneratorDumper;
use ReflectionException;
use ReflectionExtension;
use Symfony\Component\Console\Application as SymfonyApplication;

class ExtensionGeneratorCommand extends Command
{
    protected $app;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gen:ext {extension}
                    {--p|print=false : 屏幕输出}
                    {--d|dir=ext : 保存目录，默认会创建子目录保存}
                    {--s|subdirectory=true : 创建子目录目录，默认会创建子目录保存}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'PHP extend ide helper generator';

    /**
     * Create a new command instance.
     *
     * @param SymfonyApplication $app
     */
    public function __construct(SymfonyApplication $app)
    {
        $this->app = $app;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $extension = $this->input->getArgument('extension');
        $dir = $this->input->getOption('dir');
        $subdirectory = $this->input->getOption('subdirectory');

        if (! $this->hasExtension($extension)) {
            $this->error('Extension \''.$extension.'\' not present.');
            return 0;
        }

        if (realpath($dir)) {
            $dir = realpath($dir);
        } else {
            $dir = getcwd(). DIRECTORY_SEPARATOR . $dir;
        }

        if ($subdirectory) {
            $dir .= DIRECTORY_SEPARATOR . $extension;
        }

        $print = $this->input->getOption('print');
        if ($print) {
            $this->dumperPrintScreen($extension);
        } else {
            $this->dumperFiles($extension, $dir);
        }
    }

    protected function hasExtension(string $extension): bool
    {
        return extension_loaded($extension);
    }

    /**
     * 屏幕输出
     *
     * @param string $extension  ext name
     */
    private function dumperPrintScreen(string $extension): void
    {
        try {
            $dumper = new GeneratorDumper(new ReflectionExtension($extension));
            fwrite(STDOUT, "<?php\n");
            foreach ($dumper->getGenerates() as $line) {
                fwrite(STDOUT, $line . "\n");
            }
        } catch (ReflectionException $exception) {
            $this->output->error("error: " . $exception->getMessage());
            exit(1);
        }
    }

    /**
     * @param string $extension   ext name
     * @param string $dir  保存目录
     */
    private function dumperFiles(string $extension, string $dir): void
    {
        if (is_dir($dir)) {
            if (false === $this->output->confirm("Is it covered path({$dir})?")) {
                return;
            }
        } else {
            $this->output->success("save path: {$dir}");
        }

        try {
            /** @var FilesDumper $dumper */
            $filesDumper = new FilesDumper(new ReflectionExtension($extension), $this->output);
            $filesDumper->dumpFiles($dir);
        } catch (ReflectionException $exception) {
            $this->output->error("error: " . $exception->getMessage());
            exit(1);
        }
    }
}
