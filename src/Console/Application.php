<?php

namespace IDEHelperGenerator\Console;

use Closure;
use Illuminate\Support\Str;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder;

class Application extends SymfonyApplication
{
    protected $namespace;

    /**
     * The output from the previous command.
     *
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    protected $lastOutput;

    /**
     * The console application bootstrappers.
     *
     * @var array
     */
    protected static $bootstrappers = [];

    /**
     * Create a new Artisan console application.
     *
     * @param string|null $version
     */
    public function __construct(string $version = null)
    {
        parent::__construct('ide helper generator', $version);

        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        $this->bootstrap();
    }

    /**
     * Register a console "starting" bootstrapper.
     */
    public static function starting(Closure $callback)
    {
        static::$bootstrappers[] = $callback;
    }

    /**
     * Clear the console application bootstrappers.
     */
    public static function forgetBootstrappers()
    {
        static::$bootstrappers = [];
    }

    /**
     * Run an Artisan console command by name.
     *
     * @param string $command
     * @param \Symfony\Component\Console\Output\OutputInterface $outputBuffer
     *
     * @return int
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        $parameters = collect($parameters)->prepend($command);

        $this->lastOutput = $outputBuffer ?: new BufferedOutput();

        $this->setCatchExceptions(false);

        $result = $this->run(new ArrayInput($parameters->toArray()), $this->lastOutput);

        $this->setCatchExceptions(true);

        return $result;
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        return $this->lastOutput ? $this->lastOutput->fetch() : '';
    }

    /**
     * Get the application namespace.
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function getNamespace()
    {
        if (!\is_null($this->namespace)) {
            return $this->namespace;
        }

        $root = realpath(__DIR__ . '/../../');
        $composer = json_decode(file_get_contents($root . ('/composer.json')), true);
        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (strpos(str_replace('\\', '/', realpath($root .'/'. $pathChoice)), $pathChoice) > 0) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }

    /**
     * Bootstrap the console application.
     */
    protected function bootstrap()
    {
        $paths = __DIR__ . '/../Command/';
        $namespace = $this->getNamespace();

        foreach ((new Finder())->in($paths)->files() as $command) {
            $command = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after(realpath($command . ''), realpath(__DIR__ . '/../') . \DIRECTORY_SEPARATOR)
            );
            if (is_subclass_of($command, Command::class)
                && !(new ReflectionClass($command))->isAbstract()) {
                $this->add(new $command($this));
            }
        }

        foreach (static::$bootstrappers as $bootstrapper) {
            $this->add(new $bootstrapper($this));
        }
    }
}
