<?php

namespace Pw\Command;

use Pw\Generator\AssetsGenerator;
use Pw\Generator\MethodGenerator;
use Pw\Generator\WebpackGenerator;
use Pw\Generator\ControllerGenerator;
use Pw\Generator\TwigGenerator;

use Pw\Generator\Helper\CoreHelper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "pw-generator:generate", description: "PW generator command")]
class GeneratorCommand extends Command {

    public function __construct(){
        parent::__construct();
    }

    // symfony console pw-generator:generate
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $param_controller = "src/Controller/Front/FrontController";
        $param_name = "ProtectionDesDonnees";

        $route_name = CoreHelper::camelToSnake($param_name);
        $route_url = "/".str_replace("_", "-", $route_name);

        // ---------- Begin here

        $controller = ControllerGenerator::createController($param_controller);
        $domain = CoreHelper::getDomain($controller);

        $webpack_config = WebpackGenerator::createConfig($domain);

        $assets = AssetsGenerator::createFiles($controller, $param_name);
        $index = CoreHelper::getIn($assets, "index");

        $entrypoint = AssetsGenerator::addEntrypoint($webpack_config, $index);

        $twig = TwigGenerator::createTwig([
            "name" => $param_name,
            "controller" => $controller,
            "entrypoint" => $entrypoint,
            "webpack_config" => $webpack_config,
        ]);
        $twig_template = TwigGenerator::getTemplatePath($twig);

        // add methode to controller
        MethodGenerator::insertPageMethod($controller, [
            "route_url" => $route_url,
            "route_name" => $route_name,
            "name" => $param_name,
            "twig" => $twig_template,
        ]);

        return Command::SUCCESS;
    }
}
