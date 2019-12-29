<?php
//declare(strict_types=1);

namespace IDEHelperGenerator;

use AppendIterator;
use ArrayIterator;
use Generator;
use Iterator;
use IDEHelperGenerator\ZendCode\FunctionGenerator;
use IDEHelperGenerator\ZendCode\FunctionReflection;
use ReflectionExtension;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Reflection\ClassReflection;

class FilesDumper
{
    const CONST_FILENAME = '%s/const.php';
    const FUNCTIONS_FILENAME = '%s/functions.php';
    const CLASS_FILENAME = '%s.php';

    private $reflectionExtension;
    private $docBlockGenerator;

    public function __construct(ReflectionExtension $reflectionExtension)
    {
        $this->reflectionExtension = $reflectionExtension;
    }

    public function dumpFiles($dir)
    {
//        $generates = $this->getGenerationTargets();
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
    }

//    protected function getGenerationTargets() : Iterator
    protected function getGenerates() : Iterator
    {
        yield from $this->generateConstants();
        yield from $this->generateFunctions();
        yield from $this->generateClasses();
        // interface
//        ReflectionExtension::getINIEntries — 获取ini配置
//        ReflectionExtension::getName — 获取扩展名称
//        ReflectionExtension::getVersion — 获取扩展版本号

//        get_defined_vars()
//        get_declared_traits()
//        $generates = new AppendIterator();
//        $generates->append(new ArrayIterator($this->generateConstants()));
//        $generates->append(new ArrayIterator($this->generateFunctions()));
//        $generates->append($this->generateClasses());
//
//        return $generates;
    }

    public function setDocBlockGenerator(DocBlockGenerator $docBlockGenerator)// : void
    {
        $this->docBlockGenerator = $docBlockGenerator;
    }

    public function getDocBlockGenerator() //: DocBlockGenerator
    {
        if (!$this->docBlockGenerator instanceof DocBlockGenerator) {
            $docBlockGenerator = new DocBlockGenerator('auto generated file by ide helper generator.');
            $this->docBlockGenerator = $docBlockGenerator;
        }

        return $this->docBlockGenerator;
    }

    /**
     * @return array
     */
    public function generateConstants()
    {
        $reflectionConstants = $this->reflectionExtension->getConstants();

        $constantsFiles = [];
        foreach ($reflectionConstants as $constant => $value) {
            $c = preg_split('#\\\#', $constant);

            // has namespace ?
            if (count($c) > 1) {
                list($namespaces, $constName) = array_chunk($c, count($c)-1);
                $constName = current($constName);

                $namespaceFilename = sprintf(static::CONST_FILENAME, implode(DIRECTORY_SEPARATOR, $namespaces));
                if (!isset($constantsFiles[$namespaceFilename])) {
                    $constantsFiles[$namespaceFilename] = 'namespace '. implode('\\', $namespaces) . ";\n\n";
                }
            } else {
                $namespaceFilename = sprintf(static::CONST_FILENAME, "");
                if (!isset($constantsFiles[$namespaceFilename])) {
                    $constantsFiles[$namespaceFilename] = '';
                }

                $constName = $constant;
            }

            $encodeValue = is_string($value) ? sprintf('"%s"', $value) : $value;
            $constantsFiles[$namespaceFilename] .= "const $constName = {$encodeValue};\n";
        }

        return $constantsFiles;
    }

    public function generateClasses() //: Generator
    {
        /** @var \ReflectionClass $phpClassReflection */
        foreach ($this->reflectionExtension->getClasses() as $fqcn => $phpClassReflection) {
            $classGenerator = ClassGenerator::fromReflection(new ClassReflection($phpClassReflection->getName()));
            if ($this->docBlockGenerator instanceof DocBlockGenerator) {
                $classGenerator->setDocBlock($this->docBlockGenerator);
            }
            yield static::fqcnToFilename($fqcn) => $classGenerator->generate();
        }
    }

    public function generateFunctions() //: array
    {
        $functionFiles = [];
        foreach ($this->reflectionExtension->getFunctions() as $function_name => $phpFunctionReflection) {

            $functionReflection = new FunctionReflection($function_name);

            $function_filename = sprintf(static::FUNCTIONS_FILENAME, str_replace('\\', '/', $functionReflection->getNamespaceName()));

            if (isset($functionFiles[$function_filename])) {
                $functionFiles[$function_filename] .= "\n".
                    FunctionGenerator::generateByPrototypeArray($functionReflection->getPrototype());
            } else {
                $namespaceLine = '';
                if ($namespace = $functionReflection->getNamespaceName()) {
                    $namespaceLine = "namespace {$namespace};";
                }
                $functionFiles[$function_filename] = $namespaceLine . "\n" .
                    FunctionGenerator::generateByPrototypeArray($functionReflection->getPrototype());
            }
        }

        return $functionFiles;
    }

    private static function fqcnToFilename(string $fqcn)// :string
    {
        return sprintf(static::CLASS_FILENAME, str_replace('\\', '/', $fqcn));
    }
}
