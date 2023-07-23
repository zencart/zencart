<?php

namespace Restive\Console;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;

#[AsCommand(name: 'restive:make-controller')]
class RestiveControllerMakeCommand extends GeneratorCommand
{
    use CreatesMatchingTest;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'restive:make-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Restive controller class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check if required arguments are missing
        $name = $this->argument('name');
        $model = $this->argument('model');

        if (!$name) {
            $name = $this->ask('Enter the name of the class');
            $this->input->setArgument('name', $name);
        }

        if (!$model) {
            $model = $this->ask('Enter the model name');
            $this->input->setArgument('model', $model);
        }

        return parent::execute($input, $output);
    }
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub ??= '/stubs/controller.stub';

        return $this->resolveStubPath($stub);
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
                        ? $customPath
                        : __DIR__.$stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Controllers\Api';
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in the base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        $replace["use {$controllerNamespace}\Controller\Api;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }
        return $this->qualifyModel($model);
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputOption::VALUE_REQUIRED, 'The name of the class'],
            ['model', InputOption::VALUE_REQUIRED, 'The model name'],
        ];
   }
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Force overwrite if file exists'],
        ];
    }
    /**
     * Interact further with the user if they were prompted for missing arguments.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
     protected function alreadyExists($rawName)
    {
        $name = $this->qualifyClass($rawName);

        if (file_exists(app_path($name . '.php')) && !$this->option('force')) {
            $this->error($this->type . ' already exists!');
            return true;
        }

        return false;
    }

    protected function replaceClass($stub, $name)
    {
        $modelName = $this->argument('model');

        $stub = parent::replaceClass($stub, $name);
        $stub = str_replace('{{ model }}', $modelName, $stub);

        return $stub;
    }
}
