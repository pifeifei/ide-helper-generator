<?php

namespace IDEHelperGeneratorTest\Unit;

use IDEHelperGenerator\FilesDumper;
use PHPUnit\Framework\TestCase;
use ReflectionExtension;

/**
 * @internal
 * @coversNothing
 */
final class FilesDumperTest extends TestCase
{
    /**
     * @var FilesDumper
     */
    private $generator;

    private static $tmpDir;

    public static function setUpBeforeClass(): void
    {
        self::$tmpDir = sys_get_temp_dir() . '/ide-helper-generator';
    }

    protected function setUp(): void
    {
        $this->generator = new FilesDumper(new ReflectionExtension('mbstring'));
    }

    public function testDumpFiles()
    {
        $this->generator->dumpFiles(self::$tmpDir);
        $this->assertTrue(file_exists(sprintf(FilesDumper::FUNCTIONS_FILENAME, self::$tmpDir)));
        $this->assertTrue(file_exists(sprintf(FilesDumper::CONST_FILENAME, self::$tmpDir)));
    }
}
