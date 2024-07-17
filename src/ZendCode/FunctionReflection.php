<?php

declare(strict_types=1);

/**
 * most of the parts is borrowed from zendframework/zend-code.
 *
 * @see https://github.com/zendframework/zend-code
 *
 * This source is aimed for hack to override zend-code.
 *
 * @license New BSD, code from Zend Framework
 * https://github.com/zendframework/zend-code/blob/master/LICENSE.md
 */

namespace IDEHelperGenerator\ZendCode;

use Zend\Code\Reflection\DocBlockReflection;
use Zend\Code\Reflection\FunctionReflection as BaseFunctionReflection;

class FunctionReflection extends BaseFunctionReflection
{
    public function getDocBlock(): DocBlockReflection
    {
        if (false === ($comment = $this->getDocComment())) {
            return new DocBlockReflection('');
        }

        return new DocBlockReflection($comment);
    }

    public function getParameters(): array
    {
        $parametersReflections = parent::getParameters();
        $zendReflections = [];
        while ($parametersReflections && ($parameterReflection = array_shift($parametersReflections))) {
            try {
                $instance = new ParameterReflection($this->getName(), $parameterReflection->getName());
                $zendReflections[] = $instance;
                unset($parameterReflection);
            } catch (\ReflectionException $e) {
                // error print
            }
        }
        unset($parametersReflections);

        return $zendReflections;
    }

    public function getPrototype($format = self::PROTOTYPE_AS_ARRAY)
    {
        $returnType = 'mixed';
        if ($this->hasReturnType()) {
            $reflectReturn = $this->getReturnType();
            if ($reflectReturn->allowsNull()) {
                $returnType = sprintf('?%s', $reflectReturn->getName());
            } else {
                $returnType = $reflectReturn->getName();
            }
        }

        $docBlock = $this->getDocBlock();
        if ('mixed' === $returnType && $docBlock) {
            $return = $docBlock->getTag('return');
            $returnTypes = $return->getTypes();
            $returnType = \count($returnTypes) > 1 ? implode('|', $returnTypes) : $returnTypes[0];
        }

        $prototype = [
            'namespace' => $this->getNamespaceName(),
            'name' => substr($this->getName(), \strlen($this->getNamespaceName()) + ($this->getNamespaceName() ? 1 : 0)),
            'return' => $returnType,
            'arguments' => [],
        ];

        $parameters = $this->getParameters();
        foreach ($parameters as $parameter) {
            $prototype['arguments'][$parameter->getName()] = [
                'type' => $parameter->detectType(),
                'required' => !$parameter->isOptional(),
                'by_ref' => $parameter->isPassedByReference(),
                'default' => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            ];
        }

        if (self::PROTOTYPE_AS_STRING === $format) {
            $line = $prototype['return'] . ' ' . $prototype['name'] . '(';
            $args = [];
            foreach ($prototype['arguments'] as $name => $argument) {
                $argsLine = ($argument['type']
                        ? $argument['type'] . ' '
                        : '') . ($argument['by_ref'] ? '&' : '') . '$' . $name;
                if (!$argument['required']) {
                    $argsLine .= ' = ' . var_export($argument['default'], true);
                }
                $args[] = $argsLine;
            }
            $line .= implode(', ', $args);
            $line .= ')';

            return $line;
        }

        return $prototype;
    }
}
