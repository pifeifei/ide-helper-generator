<?php


namespace IDEHelperGenerator\Contracts;


interface ExtensionDumperInterface extends HelperDumperInterface
{
    public function generateConstants();
    public function generateFunctions();
    public function generateClasses();
    public function generateReadme();
}