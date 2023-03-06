<?php

namespace Pw\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "pw:test", description: "PW test command")]
class TestCommand extends Command {

    public function __construct(){
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        echo "run";
        echo PHP_EOL;

        return Command::SUCCESS;
    }
}
