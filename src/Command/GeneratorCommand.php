<?php

namespace Pw\Command;

use Pw\Command\Resources\help\PwStr;
use Pw\Command\Resources\help\PwValidator;
use Pw\Command\Resources\help\PwGenerator;
use Symfony\Component\Console\Command\Command;
use Pw\Command\Resources\Generator\ApiGenerator;
use Pw\Command\Resources\Generator\PageGenerator;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Pw\Command\Resources\Generator\ServiceGenerator;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "pw-generator:generate", description: "PW Generator command")]
class GeneratorCommand extends Command {
    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, sprintf('Type of class to generate (e.g. <fg=yellow>page</> or <fg=yellow>api</> or <fg=yellow>service</>)'))
            ->addArgument('name', InputArgument::OPTIONAL, sprintf('Class name of the page/api/service to create or update (e.g. <fg=yellow>%s</>)', PwStr::asClassName(PwStr::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__.'/Resources/HelpGeneratorCommand.txt'))
        ;
    }

    // symfony console pw-pwGenerator:generate
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $argument = Command::getDefinition()->getArgument('name');
        $question = $this->question($argument->getDescription());
        $className = $io->askQuestion($question);
        $type = $input->getArgument('type');

        if ($type == "page") {
            PageGenerator::generate($className, $io); 
        } else if ($type == "api") {
            ApiGenerator::generate($className, $io); 
        }else if ($type == "service") {
            ServiceGenerator::generate($className, $io); 
        }

        $io->success(ucfirst($type)." generated!");

        return Command::SUCCESS;
    }

    /**
     * 
     * $default is the default answer to the question asked
     * 
     * @param string $questionText
     * @param mixed $default
     * @return array
    */
    private function question(string $questionText, mixed $default = null): Question
    {
        $question = new Question($questionText, $default);
        $question->setValidator([PwValidator::class, 'notBlank']);

        return $question;
    }

}
