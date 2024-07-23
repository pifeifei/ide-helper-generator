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

use Laminas\Code\Reflection\ParameterReflection as BaseParameterReflection;

class ParameterReflection extends BaseParameterReflection
{
    public function detectType(): ?string
    {
        if (method_exists($this, 'getType')
            && ($type = $this->getType())
            && $type->isBuiltin()
        ) {
            return $type->getName();
        }

        if (false === $this->hasType()) {
            $refType = $this->getType();
            if ($refType instanceof \ReflectionNamedType) {
                return sprintf('%s$%s', $this->isPassedByReference()? '&':($this->isVariadic()? '...': ''), $this->getName());
            }

            if ($refType instanceof \ReflectionUnionType) {
                return sprintf(
                    '%s%s$%s',
                    ($refType->allowsNull() ? '?':'').implode('|', $refType->getTypes()),
                    $this->isPassedByReference()? '&':($this->isVariadic()? '...': ''),
                    $this->getName()
                );
            }

            if ($refType instanceof \ReflectionIntersectionType) {
                return sprintf(
                    '%s%s$%s',
                    ($refType->allowsNull() ? '?':'').implode('&', $refType->getTypes()),
                    $this->isPassedByReference()? '&':($this->isVariadic()? '...': ''),
                    $this->getName()
                );
            }
        }

        if (method_exists($type, 'getName')) {
            return $type->getName();
        }

        if (method_exists($type, 'getTypes')) {
            return $type->getTypes();
        }

        if (($class = $this->getClass()) instanceof \ReflectionClass) {
            return $class->getName();
        }

        return null;
    }
}
