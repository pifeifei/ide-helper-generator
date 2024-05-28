<?php


namespace IDEHelperGenerator\Dumper;


use IDEHelperGenerator\Console\OutputStyle;
use IDEHelperGenerator\Traits\FilesTrait;
use ReflectionException;
use ReflectionExtension;

class ExtensionGenerator extends AbstractHelperGenerator
{
    use FilesTrait;

    /* @var OutputStyle */
    protected $output;

    private $extensionName;

    /* @var ReflectionExtension */
    private $phpReflection;
    /**
     * @var bool
     */
    private $print;

    public function __construct($extension, OutputStyle $output)
    {
        try {
            $extension = ucfirst(strtolower($extension));
            $this->output = $output;
            $this->phpReflection = new ReflectionExtension($extension);
            parent::__construct($this->phpReflection);
        } catch (ReflectionException $e) {
            $output->error($e->getMessage());
            exit(1);
        }
    }

    public function setPrint($isPrint = true)
    {
        $this->print = $isPrint;
        return $this;
    }

    public function isPrint()
    {
        return (bool)($this->print);
    }

    public function run()
    {
        if ($this->isPrint()) {
            $this->dumperPrintScreen();
        } else {
            $this->dumperFiles();
        }
    }

    public function getExtensionName()
    {
        return $this->extensionName;
    }

    private function getRealSavePath()
    {

        $dir = $this->getSaveDir();
        if (realpath($dir)) {
            $dir = realpath($dir);
        } else {
            $dir = getcwd(). DIRECTORY_SEPARATOR . $dir;
        }

        if ($this->getSubdirectory()) {
            $dir .= DIRECTORY_SEPARATOR . $this->getExtensionName();
        }

        return $dir;
    }

}