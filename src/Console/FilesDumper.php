<?php

declare(strict_types=1);

namespace IDEHelperGenerator\Console;

use IDEHelperGenerator\FilesDumper as BaseFilesDumper;
use Iterator;
use ReflectionExtension;

class FilesDumper extends BaseFilesDumper
{
    private $console;

    public function __construct(ReflectionExtension $reflectionExtension, OutputStyle $console)
    {
        parent::__construct($reflectionExtension);
        $this->console = $console;
    }

    protected function getGenerates(): Iterator
    {
        foreach (parent::getGenerates() as $file => $code) {
            $this->console->writeln($file);
            yield $file => $code;
        }
    }
}
