<?php
//declare(strict_types=1);

/**
 * most of the parts is borrowed from zendframework/zend-code
 * @see https://github.com/zendframework/zend-code
 *
 * This source is aimed for hack to override zend-code.
 *
 * @license New BSD, code from Zend Framework
 * https://github.com/zendframework/zend-code/blob/master/LICENSE.md
 */

namespace IDEHelperGenerator\ZendCode;

use Zend\Code\Reflection\ParameterReflection as BaseParameterReflection;

class ParameterReflection extends BaseParameterReflection
{
    public function detectType(): ?string
    {
        if (method_exists($this, 'getType')
            && ($type = $this->getType())
            && $type->isBuiltin()
        ) {
            return (string) $type;
        }

        // can be dropped when dropping PHP7 support:
        if ($this->isArray()) {
            return 'array';
        }

        // can be dropped when dropping PHP7 support:
        if ($this->isCallable()) {
            return 'callable';
        }

        if (($class = $this->getClass()) instanceof \ReflectionClass) {
            return $class->getName();
        }

        return null;
    }
}
