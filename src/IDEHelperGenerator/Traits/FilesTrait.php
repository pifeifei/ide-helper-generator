<?php


namespace IDEHelperGenerator\Traits;


use IDEHelperGenerator\Console\FilesDumper;
use IDEHelperGenerator\Console\OutputStyle;

trait FilesTrait
{

    /* @var OutputStyle */
    protected $output;

    private $saveDir;
    private $subdirectory;

    public function setSaveDir($dir)
    {
        $this->saveDir = $dir;
        return $this;
    }

    public function getSaveDir()
    {
        return $this->saveDir;
    }

    public function setSubdirectory($isSubdirectory = true)
    {
        $this->subdirectory = $isSubdirectory;
        return $this;
    }

    public function getSubdirectory()
    {
        $this->getExtensionName();

        return $this->subdirectory;
    }


    protected function dumperFiles()
    {
        $dir = $this->getRealSavePath();
        if (is_dir($dir)) {
            if (false === $this->output->confirm("Is it covered path({$dir})?")) {
                return;
            }
        } else {
            $this->output->note("save path: {$dir}");
        }

        $generates = $this->getGenerates();

        foreach ($generates as $fileName => $code) {
            $pathinfo = pathinfo($fileName);
            $codeDir = $dir . DIRECTORY_SEPARATOR . $pathinfo['dirname'];
            if (!file_exists($codeDir)) {
                mkdir($codeDir, 0777, true);
            }

            $code = $this->getDocBlockGenerator()->generate() . $code;
            file_put_contents($codeDir . DIRECTORY_SEPARATOR . $pathinfo['basename'], "<?php\n$code");
        }


//        /** @var FilesDumper $dumper */
//        $filesDumper = new FilesDumper(new ReflectionExtension($extension), $this->output);
//        $filesDumper->dumpFiles($dir);
    }
}