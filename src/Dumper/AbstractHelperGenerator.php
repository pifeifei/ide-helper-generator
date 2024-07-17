<?php

namespace IDEHelperGenerator\Dumper;

use Generator;
use IDEHelperGenerator\Contracts\HelperDumperInterface;
use IDEHelperGenerator\GeneratorDumper;
use IDEHelperGenerator\ZendCode\FunctionGenerator;
use IDEHelperGenerator\ZendCode\FunctionReflection;
use ReflectionExtension;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Reflection\ClassReflection;

abstract class AbstractHelperGenerator implements HelperDumperInterface
{
    public const CONST_FILENAME = '%s/const.php';
    public const FUNCTIONS_FILENAME = '%s/functions.php';
    public const CLASS_FILENAME = '%s.php';
    protected $reflectionExtension;
    protected $docBlockGenerator;

    public function __construct(ReflectionExtension $reflectionExtension)
    {
        $this->reflectionExtension = $reflectionExtension;
    }

    public function getGenerates(): Generator
    {
        yield from $this->generateConstants();
        yield from $this->generateFunctions();
        yield from $this->generateClasses();
    }

    public function setDocBlockGenerator(DocBlockGenerator $docBlockGenerator)
    {
        $this->docBlockGenerator = $docBlockGenerator;
    }

    public function getDocBlockGenerator(): DocBlockGenerator
    {
        if (!$this->docBlockGenerator instanceof DocBlockGenerator) {
            $docBlockGenerator = new DocBlockGenerator('auto generated file by ide helper generator.');
            $this->docBlockGenerator = $docBlockGenerator;
        }

        return $this->docBlockGenerator;
    }

    /**
     * @return Generator
     */
    public function generateConstants()
    {
        $reflectionConstants = $this->reflectionExtension->getConstants();

        $declaredNamespaces = [];
        foreach ($reflectionConstants as $constant => $value) {
            $c = preg_split('#\\\#', $constant);

            // has namespace ?
            if (\count($c) > 1) {
                [$namespaces, $constName] = array_chunk($c, \count($c) - 1);
                $constName = current($constName);

                $namespace = implode('\\', $namespaces);
                if (!isset($declaredNamespaces[$namespace])) {
                    $declaredNamespaces[$namespace] = true;
                    yield "namespace {$namespace};";
                }

                $encodeValue = \is_string($value) ? sprintf('"%s"', $value) : $value;
                yield "const {$constName} = {$encodeValue};";
            } else {
                $encodeValue = \is_string($value) ? sprintf('"%s"', $value) : $value;
                yield "const {$constant} = {$encodeValue};";
            }
        }
    }

    public function generateClasses(): Generator
    {
        /** @var \ReflectionClass $phpClassReflection */
        foreach ($this->reflectionExtension->getClasses() as $fqcn => $phpClassReflection) {
            $classGenerator = ClassGenerator::fromReflection(new ClassReflection($phpClassReflection->getName()));

            yield $classGenerator->generate();
        }

//        return "";
    }

    public function generateFunctions()
    {
        $functionFiles = [];
        foreach ($this->reflectionExtension->getFunctions() as $function_name => $phpFunctionReflection) {
            $functionReflection = new FunctionReflection($function_name);

            $function_filename = sprintf(static::FUNCTIONS_FILENAME, str_replace('\\', '/', $functionReflection->getNamespaceName()));

            if (isset($functionFiles[$function_filename])) {
                $functionFiles[$function_filename] .= "\n" .
                    FunctionGenerator::generateByPrototypeArray($functionReflection->getPrototype());
            } else {
                $namespaceLine = '';
                if ($namespace = $functionReflection->getNamespaceName()) {
                    $namespaceLine = "namespace {$namespace};";
                }
                $functionFiles[$function_filename] = $namespaceLine . "\n" .
                    FunctionGenerator::generateByPrototypeArray($functionReflection->getPrototype());
            }
        }[
            'extension' => '', // 所属扩展,首字母大写
            'namespace' => '', // 创建子文件夹,首字母大写
                'interface' => '', // 文件名+interface
                'trait' => '', // 文件名+trait
                'class' => '',
                'function' => '',
                'const' => '',
            'source' => '',
        ];

        return $functionFiles;
//        $declaredNamespaces = [];
//        foreach ($this->reflectionExtension->getFunctions() as $function_name => $phpFunctionReflection) {
//            try {
//                $functionReflection = new FunctionReflection($function_name);
//
//                $namespace = $functionReflection->getNamespaceName();
//                if ($namespace && !isset($declaredNamespaces[$namespace])) {
//                    $declaredNamespaces[$namespace] = true;
//                    yield "namespace {$namespace};";
//                }
//
//                yield FunctionGenerator::generateByPrototypeArray($functionReflection->getPrototype());
//            } catch (ReflectionException $e) {
//                dump('error: ' . $e->getMessage());
//                exit(1);
//            }
//        }
    }

    abstract public function run();

    /**
     * 屏幕输出。
     */
    protected function dumperPrintScreen()
    {
        $dumper = new GeneratorDumper($this->reflectionExtension);
        fwrite(STDOUT, "<?php\n");
        foreach ($dumper->getGenerates() as $line) {
            fwrite(STDOUT, $line . "\n");
        }
    }
}
