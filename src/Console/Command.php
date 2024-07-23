<?php

namespace IDEHelperGenerator\Console;

use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface as SymfonyInputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface as SymfonyOutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Command extends SymfonyCommand
{
    /**
     * The input interface implementation.
     *
     * @var SymfonyInputInterface
     */
    protected $input;

    /**
     * The output interface implementation.
     *
     * @var OutputStyle
     */
    protected $output;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * Indicates whether the command should be shown in the Artisan command list.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * The default verbosity of output commands.
     *
     * @var int
     */
    protected $verbosity = SymfonyOutputInterface::VERBOSITY_NORMAL;

    /**
     * The mapping between human-readable verbosity levels and Symfony's OutputInterface.
     *
     * @var array
     */
    protected $verbosityMap = [
        'v' => SymfonyOutputInterface::VERBOSITY_VERBOSE,
        'vv' => SymfonyOutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv' => SymfonyOutputInterface::VERBOSITY_DEBUG,
        'quiet' => SymfonyOutputInterface::VERBOSITY_QUIET,
        'normal' => SymfonyOutputInterface::VERBOSITY_NORMAL,
    ];

    /**
     * Create a new console command instance.
     */
    public function __construct()
    {
        // We will go ahead and set the name, description, and parameters on console
        // commands just to make things a little easier on the developer. This is
        // so they don't have to all be manually specified in the constructors.
        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($this->name);
        }

        // Once we have constructed the command, we'll set the description and other
        // related properties of the command. If a signature wasn't used to build
        // the command we'll set the arguments and the options on this command.
        $this->setDescription($this->description);

        $this->setHidden($this->hidden);

        if (!isset($this->signature)) {
            $this->specifyParameters();
        }
    }

    /**
     * Run the console command.
     */
    public function run(SymfonyInputInterface $input, SymfonyOutputInterface $output): int
    {
        return parent::run(
            $this->input = $input,
            $this->output = new OutputStyle($input, $output)
        );
    }

    /**
     * Call another console command.
     */
    public function call(string $command, array $arguments = []): int
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(
            $this->createInputFromArguments($arguments),
            $this->output
        );
    }

    /**
     * Call another console command silently.
     */
    public function callSilent(string $command, array $arguments = []): int
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(
            $this->createInputFromArguments($arguments),
            new NullOutput()
        );
    }

    /**
     * Determine if the given argument is present.
     *
     * @param int|string $name
     */
    public function hasArgument($name): bool
    {
        return $this->input->hasArgument($name);
    }

    /**
     * Get the value of a command argument.
     *
     * @return array|string
     */
    public function argument(string $key = null)
    {
        if (\is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get all the arguments passed to the command.
     *
     * @return array
     */
    public function arguments()
    {
        return $this->argument();
    }

    /**
     * Determine if the given option is present.
     */
    public function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }

    /**
     * Get the value of a command option.
     *
     * @return array|string
     */
    public function option(string $key = null)
    {
        if (\is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Get all the options passed to the command.
     *
     * @return array
     */
    public function options()
    {
        return $this->option();
    }

    /**
     * Confirm a question with the user.
     */
    public function confirm(string $question, bool $default = false): bool
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     */
    public function ask(string $question, string $default = null): string
    {
        return $this->output->ask($question, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     */
    public function anticipate(string $question, array $choices, string $default = null): string
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     */
    public function askWithCompletion(string $question, array $choices, string $default = null): string
    {
        $question = new Question($question, $default);

        $question->setAutocompleterValues($choices);

        return $this->output->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     */
    public function secret(string $question, bool $fallback = true): string
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param mixed $attempts
     * @param bool $multiple
     */
    public function choice(string $question, array $choices, string $default = null, $attempts = null, bool $multiple = null): string
    {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->output->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param array|Arrayable $rows
     */
    public function table(array $headers, $rows, string $tableStyle = 'default', array $columnStyles = []): void
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders((array) $headers)->setRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    /**
     * Write a string as information output.
     *
     * @param null|int|string $verbosity
     */
    public function info(string $string, $verbosity = null): void
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as standard output.
     *
     * @param null|int|string $verbosity
     */
    public function line(string $string, string $style = null, $verbosity = null): void
    {
        $styled = $style ? "<{$style}>{$string}</{$style}>" : $string;

        $this->output->writeln($styled, $this->parseVerbosity($verbosity));
    }

    /**
     * Write a string as comment output.
     *
     * @param null|int|string $verbosity
     */
    public function comment(string $string, $verbosity = null): void
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as question output.
     *
     * @param null|int|string $verbosity
     */
    public function question(string $string, $verbosity = null): void
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param null|int|string $verbosity
     */
    public function error(string $string, $verbosity = null): void
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     *
     * @param null|int|string $verbosity
     */
    public function warn(string $string, $verbosity = null): void
    {
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');

            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosity);
    }

    /**
     * Write a string in an alert box.
     */
    public function alert(string $string): void
    {
        $this->comment(str_repeat('*', \strlen($string) + 12));
        $this->comment('*     ' . $string . '     *');
        $this->comment(str_repeat('*', \strlen($string) + 12));

        $this->output->newLine();
    }

    /**
     * Get the output implementation.
     *
     * @return SymfonyOutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set the Laravel application instance.
     *
     * @param \Illuminate\Contracts\Container\Container $laravel
     */
    public function setLaravel($laravel)
    {
        $this->laravel = $laravel;
    }

    /**
     * Configure the console command using a fluent definition.
     */
    protected function configureUsingFluentDefinition()
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        parent::__construct($this->name = $name);

        // After parsing the signature we will spin through the arguments and options
        // and set them on this command. These will already be changed into proper
        // instances of these "InputArgument" and "InputOption" Symfony classes.
        foreach ($arguments as $argument) {
            $this->getDefinition()->addArgument($argument);
        }

        foreach ($options as $option) {
            $this->getDefinition()->addOption($option);
        }
    }

    /**
     * Specify the arguments and options on the command.
     */
    protected function specifyParameters()
    {
        // We will loop through all the arguments and options for the command and
        // set them all on the base command instance. This specifies what can get
        // passed into these commands as "parameters" to control the execution.
        foreach ($this->getArguments() as $arguments) {
            \call_user_func_array([$this, 'addArgument'], $arguments);
        }

        foreach ($this->getOptions() as $options) {
            \call_user_func_array([$this, 'addOption'], $options);
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    protected function execute(SymfonyInputInterface $input, SymfonyOutputInterface $output)
    {
        // TODO : handle() 不能带有参数
        return \call_user_func([$this, 'handle']);
    }

    /**
     * Create an input instance from the given arguments.
     */
    protected function createInputFromArguments(array $arguments): ArrayInput
    {
        return tap(new ArrayInput($arguments), function ($input) {
            if ($input->hasParameterOption(['--no-interaction'], true)) {
                $input->setInteractive(false);
            }
        });
    }

    /**
     * Set the verbosity level.
     *
     * @param int|string $level
     */
    protected function setVerbosity($level): void
    {
        $this->verbosity = $this->parseVerbosity($level);
    }

    /**
     * Get the verbosity level in terms of Symfony's OutputInterface level.
     *
     * @param int|string $level
     */
    protected function parseVerbosity($level = null): int
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (!\is_int($level)) {
            $level = $this->verbosity;
        }

        return $level;
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [];
    }
}
