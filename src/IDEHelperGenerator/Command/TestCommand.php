<?php

namespace IDEHelperGenerator\Command;

use Composer\Console\Application as ComposerApplication;
use IDEHelperGenerator\Console\Command;
use IDEHelperGenerator\Dumper\AbstractHelperGenerator;
use IDEHelperGenerator\IDEHelperManager;
use Symfony\Component\Console\Application as SymfonyApplication;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Reflection\FileReflection;

class TestCommand extends Command
{
    protected $app;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:command  {extension}
                    {--p|print=false : 屏幕输出}
                    {--d|dir=ext : 保存目录，默认会创建子目录保存}
                    {--s|subdirectory=true : 创建子目录目录，默认会创建子目录保存}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test Command description';

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

    public function handle()
    {
//        $print = $this->input->getOption('print');
//        $extension = $this->argument('extension');
//
//        $dumper = IDEHelperManager::createExtensionGenerator($extension, $this->output);
//
//        $dumper->setSaveDir($this->input->getOption('dir'))
//            ->setSubdirectory($this->input->getOption('subdirectory'))
//            ->setPrint($print === false ? false : true);
//
//        $dumper->run();

        $ref = new \ReflectionFunction('array_add');
        $file = ($ref->getFileName());
        dump($file);

        $reflection = new FileReflection($file);
        dump($reflection->getClasses());
        dump($reflection->getFunctions());
        dump($reflection->getFileName());

        //        dump($ref->getDocComment());
//        $reflection = FileGenerator::fromReflectedFileName($file);
////        $reflection->setDocBlock('xxx xfd');
////        $reflection->setClass("PDO");
////        dump($reflection->generate());
////        dump($reflection->getClasses());
//        dump($reflection->get());
////        dump($reflection->generate());
//        $ref = new \ReflectionFunction('array_add');
//        $ref = new \ReflectionFunction('strposs');
//        var_dump($ref->getFileName());
//        $ref = new \ReflectionExtension('ftp');
//        $funs = $ref->getFunctions();
//        foreach ($funs as $fun) {
//            var_dump($fun);
//        }
//        dump(get_class($dumper));
//        fwrite(STDOUT, "<?php\n");
//        foreach ($dumper->getGenerates() as $line) {
//            fwrite(STDOUT, $line . "\n");
//        }
//        dump($this->getApplication()->has('test:command'));
////        var_dump($this->getApplication()->all('test'));
////        dump(get_included_files());
//
//        $generator = FileGenerator::fromReflectedFileName(__DIR__. '/../../../tests/PHPExtensionStubGeneratorTest/FilesDumperTest.php');
////        $generator->setFilename('xxx');
//        dump($generator->getNamespace());
//        dump($_SERVER['argv']);
//        $composer = new ComposerApplication();
//        $composer = new \Composer\Composer();
//        dump($composer->getDownloadManager());
//        stream_set_blocking();
//        get_defined_constants();
//        dump($generator->getClasses());
//        dump(get_included_files());
//        dump($generator->write());
    }
}
