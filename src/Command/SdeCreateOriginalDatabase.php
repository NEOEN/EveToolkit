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

class SdeCreateOriginalDatabase extends Command
{
    const ALLOWED_ENVIRONMENTS = [
        'dev'
    ];

    protected function configure()
    {
        $this
            ->setName('neoen:eve:toolkit:create-original-database')
            ->setDescription('Create original sde database.')
            ->setHelp(<<<EOT
Create original sde database.

Uses system() call to mysql. Only available in dev environment.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!in_array($input->getOption('env'), self::ALLOWED_ENVIRONMENTS)) {
            $output->write("Can only be used in the following environments: " . implode(', ', self::ALLOWED_ENVIRONMENTS), true);
            return;
        }

        //@todo error handling
        system("sudo mysql -e 'DROP DATABASE IF EXISTS sde_original'");
        system("sudo mysql -e 'CREATE DATABASE sde_original'");
        system("sudo mysql sde_original < " . SdeDownloadCommand::BASE_PATH . "/sde_original.sql");

        $output->write("db updated", true);
    }
}
