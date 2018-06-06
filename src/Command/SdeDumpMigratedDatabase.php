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

class SdeDumpMigratedDatabase extends Command
{
    const ALLOWED_ENVIRONMENTS = [
        'dev'
    ];

    protected function configure()
    {
        $this
            ->setName('neoen:eve:toolkit:dump-migrated-database')
            ->setDescription('Create a backup of the migrated database.')
            ->setHelp(<<<EOT
Create a backup of the migrated database..

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
        system("sudo mysqldump evetoolkit > ./resources/migrated-sde.sql");

        $output->write("db updated", true);
    }
}
