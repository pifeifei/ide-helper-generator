<?php


namespace IDEHelperGenerator;


use IDEHelperGenerator\Console\OutputStyle;
use IDEHelperGenerator\Dumper\ExtensionGenerator;

class IDEHelperManager
{
    private static $helperGenerator;

    private static $extensionName;

    public static function createExtensionGenerator($extensionName, OutputStyle $output): ExtensionGenerator
    {
        static::$extensionName = $extensionName;

        return static::$helperGenerator = new ExtensionGenerator($extensionName, $output);
    }
}
