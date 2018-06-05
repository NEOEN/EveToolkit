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

class CreateOriginalSdeDatabase extends Command
{
    protected function configure()
    {
        $this
            ->setName('neoen:eve:toolkit:create-original-database')
            ->setDescription('Create original sde database.')
            ->setHelp(<<<EOT
Create original sde database.

Uses system() call with sudo for now. Only available in local and dev environment.
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
