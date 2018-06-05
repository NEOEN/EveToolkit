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

class SdeDownloadCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('neoen:eve:toolkit:download-sde')
            ->setDescription('Fetch latest sde.')
            ->setDefinition(array(
                new InputArgument(
                    'release-date', InputArgument::REQUIRED, 'The release date in the form yyyymmdd'
                ),
                new InputArgument(
                    'datasource', InputArgument::OPTIONAL, 'The data environment (tranquility for production data)', 'tranquility'
                )
            ))
            ->setHelp(<<<EOT
Fetch latest sde.

Uses system() calls. Only available in local and dev environment.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // @todo error handling
        $client = new Client();

        $url = "https://cdn1.eveonline.com/data/sde/" . $input->getArgument('datasource') . "/sde-" . $input->getArgument('release-date') ."-" . mb_strtoupper($input->getArgument('datasource')) .".zip";
        $client->get($url, array('save_to' => "./sde-" . $input->getArgument('release-date') ."-" . mb_strtoupper($input->getArgument('datasource')) .".zip"));
        system("unzip ./sde-" . $input->getArgument('release-date') ."-" . mb_strtoupper($input->getArgument('datasource')) .".zip");
        $url = "https://www.fuzzwork.co.uk/dump/mysql-latest.tar.bz2";
        $client->get($url, array('save_to' => "./sde-db-latest.tar.bz2"));
        system("tar -xjf ./sde-db-latest.tar.bz2");
        system("mv ./sde-" . $input->getArgument('release-date') . "-" . mb_strtoupper($input->getArgument('datasource')) . "/sde-" . $input->getArgument('release-date') . "-" . mb_strtoupper($input->getArgument('datasource')) . ".sql ./sde/sde_original.sql");
        system("rm -Rf ./sde-" . $input->getArgument('release-date') . "-" . mb_strtoupper($input->getArgument('datasource')));

        $output->write("Downloaded", true);
    }
}
