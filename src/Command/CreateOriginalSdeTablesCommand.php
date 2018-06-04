<?php
/**
 * Created by IntelliJ IDEA.
 * User: jan.schumann
 * Date: 02.06.18
 * Time: 09:57
 */

namespace App\Command;


use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateOriginalSdeTablesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('neoen:eve:toolkit:createOriginalSdeDB')
            ->setDescription('Create sde db')
            ->setHelp(<<<EOT
Create sde db.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //@todo error handling
        system("sudo mysql -e 'DROP DATABASE IF EXISTS sde_original'");
        system("sudo mysql -e 'CREATE DATABASE sde_original'");
        system("sudo mysql sde_original < sde/sde_original.sql");

        $output->write("db updated", true);
    }
}
