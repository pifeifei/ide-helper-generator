<?php

declare(strict_types=1);

namespace IDEHelperGenerator;

use IDEHelperGenerator\ZendCode\FunctionGenerator;
use IDEHelperGenerator\ZendCode\FunctionReflection;
use Iterator;
use ReflectionExtension;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Reflection\ClassReflection;

class FilesDumper
{
    public const CLASS_ALIAS_FILENAME = 'class_alias.php';
    public const CONST_FILENAME = 'const.php';
    public const FUNCTIONS_FILENAME = 'functions.php';
    public const CLASS_FILENAME = '%s.php';

    /** @var array<string, class-string> */
    protected $classAlias = [];
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
            $codeDir = $dir . \DIRECTORY_SEPARATOR . $pathinfo['dirname'];
            if (!file_exists($codeDir)) {
                mkdir($codeDir, 0777, true);
            }

            $code = $this->getDocBlockGenerator()->generate() . $code;
            file_put_contents($codeDir . \DIRECTORY_SEPARATOR . $pathinfo['basename'], "<?php\n{$code}");
        }
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
     * @return string[]
     */
    public function generateConstants(): array
    {
        $reflectionConstants = $this->reflectionExtension->getConstants();

        $constantsFiles = [];
        foreach ($reflectionConstants as $constant => $value) {
            $c = preg_split('#\\\#', $constant);

            // has namespace ?
            if (\count($c) > 1) {
                [$namespaces, $constName] = array_chunk($c, \count($c) - 1);
                $constName = current($constName);

                $namespaceFilename = sprintf(static::CONST_FILENAME, implode(\DIRECTORY_SEPARATOR, $namespaces));
                if (!isset($constantsFiles[$namespaceFilename])) {
                    $constantsFiles[$namespaceFilename] = 'namespace ' . implode('\\', $namespaces) . ";\n\n";
                }
            } else {
                $namespaceFilename = sprintf(static::CONST_FILENAME, '');
                if (!isset($constantsFiles[$namespaceFilename])) {
                    $constantsFiles[$namespaceFilename] = '';
                }

                $constName = $constant;
            }

            $encodeValue = $this->encodeValue($value);
            $constantsFiles[$namespaceFilename] .= "const {$constName} = {$encodeValue};\n";
        }

        return $constantsFiles;
    }

    protected function encodeValue($value)
    {
//        \is_string($value) ? sprintf('\'%s\'', $value) : $value;
        if (is_string($value)) {
            return sprintf('\'%s\'', $value);
        }

        if (is_bool($value)) {
            return $value ? 'true': 'false';
        }
        if (is_null($value)) {
            return 'null';
        }

        return $value;
    }

    /**
     * @throws \ReflectionException
     */
    public function generateClasses() //: Generator
    {
        foreach ($this->reflectionExtension->getClasses() as $classKey => $phpClassReflection) {
            $classGenerator = ClassGenerator::fromReflection(new ClassReflection($phpClassReflection->getName()));

            if ($classKey !== $phpClassReflection->getName()) {
                $this->classAlias[$classKey] = $phpClassReflection->getName();
                continue;
            }
            yield static::classKeyToFilename($classKey) => $classGenerator->generate();
        }
    }

    public function generateAlias()
    {
        if (count($this->classAlias) === 0) {
            return [];
        }


        $str = '';
        array_walk($this->classAlias, function($funName, $className) use (& $str){
            $str .= sprintf("class_alias(%s::class, %s::class);", $funName, $className).PHP_EOL;
        });
        return [static::CLASS_ALIAS_FILENAME => $str];
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function generateFunctions(): array
    {
        $functionFiles = [];
        foreach ($this->reflectionExtension->getFunctions() as $function_name => $phpFunctionReflection) {
            $functionReflection = new FunctionReflection($function_name);

            $funFilename = sprintf(static::FUNCTIONS_FILENAME, str_replace('\\', '/', $functionReflection->getNamespaceName()));

            if (isset($functionFiles[$funFilename])) {
                $functionFiles[$funFilename] .= "\n" .
                    FunctionGenerator::generateByPrototypeArray($functionReflection->getPrototype());
            } else {
                $namespaceLine = '';
                if ($namespace = $functionReflection->getNamespaceName()) {
                    $namespaceLine = "namespace {$namespace};";
                }
                $functionFiles[$funFilename] = $namespaceLine . "\n" .
                    FunctionGenerator::generateByPrototypeArray($functionReflection->getPrototype());
            }
        }

        return $functionFiles;
    }

    protected function getGenerates(): Iterator
    {
        yield from $this->generateConstants();
        yield from $this->generateFunctions();
        yield from $this->generateClasses();
        yield from $this->generateAlias();
    }

    private static function classKeyToFilename(string $classKey): string
    {
        return sprintf(static::CLASS_FILENAME, str_replace('\\', '/', $classKey));
    }
}
