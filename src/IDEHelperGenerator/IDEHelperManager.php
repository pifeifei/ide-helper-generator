<?php


namespace IDEHelperGenerator;


use IDEHelperGenerator\Console\OutputStyle;
use IDEHelperGenerator\Dumper\ExtensionGenerator;
use IDEHelperGenerator\Dumper\PackageGeneratorAbstract;

class IDEHelperManager
{
    private static $helperGenerator;

    private static $extensionName;

    private static $packageName;

    public static function createExtensionGenerator($extensionName, OutputStyle $output)
    {
        static::$extensionName = $extensionName;

        return static::$helperGenerator = new ExtensionGenerator($extensionName, $output);
    }

    public static function createPackageGenerator($packageName, OutputStyle $output)
    {
        static::$packageName = $packageName;

        return static::$helperGenerator = new PackageGeneratorAbstract($packageName, $output);
    }
}