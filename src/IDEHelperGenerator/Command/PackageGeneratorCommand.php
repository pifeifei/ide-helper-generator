<?php

namespace IDEHelperGenerator\Command;

use IDEHelperGenerator\Console\Command;
use IDEHelperGenerator\Console\FilesDumper;
use IDEHelperGenerator\Console\Parser;
use IDEHelperGenerator\GeneratorDumper;
use ReflectionException;
use ReflectionExtension;
use Symfony\Component\Console\Application as SymfonyApplication;

class PackageGeneratorCommand extends Command
{
    protected $app;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:package {packageName}
                    {--p|print=false : 屏幕输出}
                    {--d|dir=ext : 保存目录，默认会创建子目录保存}
                    {--s|subdirectory=true : 创建子目录目录，默认会创建子目录保存}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TODO: ide helper generator for composer package.';

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
     * @return mixed|void
     */
    public function handle()
    {
        $this->output->note('TODO');
    }

}
