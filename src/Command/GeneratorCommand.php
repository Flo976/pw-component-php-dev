<?php

namespace Pw\Command;

use Pw\Command\Resources\help\PwStr;
use Pw\Command\Resources\help\PwValidator;
use Pw\Command\Resources\help\PwGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Pw\Command\Resources\Generator\ApiGenerator;
use Symfony\Component\Console\Question\Question;
use Pw\Command\Resources\Generator\PageGenerator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Pw\Command\Resources\Generator\ApiV1Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Pw\Command\Resources\Generator\PageV1Generator;
use Symfony\Component\Console\Input\InputInterface;
use Pw\Command\Resources\Generator\ServiceGenerator;
use Symfony\Component\Console\Output\OutputInterface;
use Pw\Command\Resources\Generator\ServiceV1Generator;

#[AsCommand(name: "pw-generator:generate", description: "PW Generator command")]
class GeneratorCommand extends Command {
    protected function configure(): void
    {  
        $method = "index";
        $term = PwStr::asClassName(PwStr::getRandomTerm());
        $type = strtolower(PwStr::asClassName(PwStr::getRandomType()));
        $route_name = $type."_".strtolower($term)."_".$method;
        $uri = PwStr::asRoutePath($route_name);
        $twig = PwStr::asRoutePath($route_name).".html.twig";
        $this
            ->addArgument('type', InputArgument::REQUIRED, sprintf('Type of class to generate a page or an API or a service (e.g. <fg=yellow>%s</>)', $type))
            ->addArgument('name', InputArgument::OPTIONAL, sprintf('Class name of the page/api/service to create or update (e.g. <fg=yellow>%s</>)', $term))
            ->addArgument('method', InputArgument::OPTIONAL, sprintf('Method of the page/api/service to create or update (e.g. <fg=yellow>%s</>)', $method), $method)
            ->addOption('route-name', 'r', InputOption::VALUE_OPTIONAL, 'Add the route name of the method (e.g. <fg=yellow>'.$route_name.'</>)')
            ->addOption('uri', 'u', InputOption::VALUE_OPTIONAL, 'Add the route url of the method (e.g. <fg=yellow>'.$uri.'</>)')
            ->addOption('request',null, InputOption::VALUE_OPTIONAL, 'Add the HTTP request methods POST/GET', 'POST, GET')
            ->addOption('twig', 't', InputOption::VALUE_OPTIONAL, 'Add the twig path file of the method (e.g. <fg=yellow>'.$twig.'</>)')
            ->addOption('methods','m', InputOption::VALUE_OPTIONAL, 'Add the list of the method (e.g <fg=yellow>save, load, list</>)')
            ->setHelp(file_get_contents(__DIR__.'/Resources/HelpGeneratorCommand.txt'))
        ;
    }

    // symfony console pw-pwGenerator:generate
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $isLineCommand = true;
        $io = new SymfonyStyle($input, $output);
        $className = $input->getArgument('name');
        if(!$input->getArgument('name')) {
            $isLineCommand = false;
            $argument = Command::getDefinition()->getArgument('name');
            $question = $this->question($argument->getDescription());
            $className = $io->askQuestion($question);
        }

        $type = $input->getArgument('type');
        $route_url = $input->getOption('uri');
        $twig_path = $input->getOption('twig');
        $request = $input->getOption('request');
        $methods = $input->getOption('methods');
        $route_name = $input->getOption('route-name');
        $methodName = strtolower($input->getArgument('method'));

        $data = [
            'type' => $type,
            'request' => $request,
            'methods' => $methods,
            'route_url' => $route_url,
            'twig_path' => $twig_path,
            'className' => $className,
            'route_name' => $route_name,
            'methodName' => $methodName
        ];

        if ($type == "page") {
            if(!$isLineCommand ) {
                PageGenerator::generate($className, $type, $io);
            } else {
                PageV1Generator::generate($data, $io);
            }
        } else if ($type == "api") {
            if(!$isLineCommand ) {
                ApiGenerator::generate($className, $type, $io); 
            } else {
                ApiV1Generator::generate($data, $io);
            }
        }else if ($type == "service") {
            if(!$isLineCommand ) {
                ServiceGenerator::generate($className, $io); 
            } else {
                ServiceV1Generator::generate($className, $methods, $io);
            }
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
