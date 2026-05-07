<?php

namespace LaravelDdd\Starter\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DddTestCommand extends Command
{
    protected $signature = 'ddd:test {--filter= : Filter which tests to run}
                           {--stop-on-failure : Stop after first failure}';

    protected $description = 'Run package tests';

    public function handle(): int
    {
        $command = ['./vendor/bin/phpunit'];

        if ($this->option('filter')) {
            $command[] = '--filter=' . $this->option('filter');
        }

        if ($this->option('stop-on-failure')) {
            $command[] = '--stop-on-failure';
        }

        $process = new Process($command);
        $process->setTty(Process::isTtySupported());
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->getExitCode();
    }
}
