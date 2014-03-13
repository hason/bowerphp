<?php

/*
 * This file is part of Bowerphp.
 *
 * (c) Massimiliano Arione <massimiliano.arione@bee-lab.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bowerphp\Console;

use Bowerphp\Command;
use Composer\Command\Helper\DialogHelper;
use Composer\Util\ErrorHandler;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The console application that handles the commands
 * Inspired by Composer https://github.com/composer/composer
 */
class Application extends BaseApplication
{
    /**
     * @var Bowerphp
     */
    protected $bowerphp;

    private static $logo = '    ____                                __
   / __ )____ _      _____  _________  / /_  ____
  / __  / __ \ | /| / / _ \/ ___/ __ \/ __ \/ __ \
 / /_/ / /_/ / |/ |/ /  __/ /  / /_/ / / / / /_/ /
/_____/\____/|__/|__/\___/_/  / .___/_/ /_/ .___/
                             /_/         /_/
';

    /**
     * Constructor
     */
    public function __construct()
    {
        if (function_exists('ini_set')) {
            ini_set('xdebug.show_exception_trace', false);
            ini_set('xdebug.scream', false);

        }
        if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
            date_default_timezone_set(@date_default_timezone_get());
        }

        ErrorHandler::register();
        parent::__construct('Bowerphp', '0.1 Powered by BeeLab (bee-lab.net)');
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (version_compare(PHP_VERSION, '5.3.2', '<')) {
            $output->writeln('<warning>Bowerphp only officially supports PHP 5.3.2 and above, you will most likely encounter problems with your PHP '.PHP_VERSION.', upgrading is strongly recommended.</warning>');
        }

        if ($input->hasParameterOption('--profile')) {
            $startTime = microtime(true);
        }

        if ($newWorkDir = $this->getNewWorkingDir($input)) {
            $oldWorkingDir = getcwd();
            chdir($newWorkDir);
        }

        $result = parent::doRun($input, $output);

        if (isset($oldWorkingDir)) {
            chdir($oldWorkingDir);
        }

        if (isset($startTime)) {
            $output->writeln('<info>Memory usage: '.round(memory_get_usage() / 1024 / 1024, 2).'MB (peak: '.round(memory_get_peak_usage() / 1024 / 1024, 2).'MB), time: '.round(microtime(true) - $startTime, 2).'s');
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getHelp()
    {
        return self::$logo . parent::getHelp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultCommands()
    {
        return array(
            new HelpCommand(),
            new Command\CommandListCommand(),
            new Command\HomeCommand(),
            new Command\InfoCommand(),
            new Command\InitCommand(),
            new Command\InstallCommand(),
            new Command\ListCommand(),
            new Command\LookupCommand(),
            new Command\SearchCommand(),
            new Command\UpdateCommand(),
            new Command\UninstallCommand(),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('--profile', null, InputOption::VALUE_NONE, 'Display timing and memory usage information'));
        $definition->addOption(new InputOption('--working-dir', '-d', InputOption::VALUE_REQUIRED, 'If specified, use the given directory as working directory.'));

        return $definition;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultHelperSet()
    {
        $helperSet = parent::getDefaultHelperSet();

        $helperSet->set(new DialogHelper());

        return $helperSet;
    }

    /**
     * @param  InputInterface    $input
     * @throws \RuntimeException
     */
    private function getNewWorkingDir(InputInterface $input)
    {
        $workingDir = $input->getParameterOption(array('--working-dir', '-d'));
        if (false !== $workingDir && !is_dir($workingDir)) {
            throw new \RuntimeException('Invalid working directory specified.');
        }

        return $workingDir;
    }
}
