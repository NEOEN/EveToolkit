<?php

namespace App\Command;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use SchumannIt\DBAL\Schema\Converter\ConverterChain;
use SchumannIt\DBAL\Schema\Converter\DoctrineConverter;
use SchumannIt\DBAL\Schema\Converter\EnsureAutoIncrementPrimaryKeyConverter;
use SchumannIt\DBAL\Schema\Converter\RenamePrimaryKeyIfSingleColumnIndex;
use SchumannIt\DBAL\Schema\Mapping;
use SchumannIt\DBAL\Schema\Migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use	Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSchemaMigrationCommand extends Command
{
    /**
     * @var Migration
     */
    protected $migration;

    protected function configure()
    {
        $this
        ->setDefinition(array(
            new InputOption(
                'source-host', null, InputOption::VALUE_REQUIRED, 'Host of the source  database in the form".', 'localhost'
            ),
            new InputOption(
                'source-driver', null, InputOption::VALUE_REQUIRED, 'The driver.', 'pdo_mysql'
            ),
            new InputOption(
                'source-dbname', null, InputOption::VALUE_REQUIRED, 'DB name of the source', 'sde_original'
            ),
            new InputOption(
                'source-user', null, InputOption::VALUE_REQUIRED, 'User name with access to the source database.', 'root'
            ),
            // @todo make password secure
            new InputOption(
                'source-password', null, InputOption::VALUE_REQUIRED, 'Password for the source database.', ''
            ),
            new InputOption(
                'source-port', null, InputOption::VALUE_REQUIRED, 'Port of the source database..', '3306'
            ),
            new InputOption(
                'target-host', null,InputOption::VALUE_REQUIRED, 'Host of the target database".', 'localhost'
            ),
            new InputOption(
                'target-driver', null, InputOption::VALUE_REQUIRED, 'Db type of the target db.', 'pdo_mysql'
            ),
            new InputOption(
                'target-dbname', null, InputOption::VALUE_REQUIRED, 'DB name of the target database.', 'sde'
            ),
            new InputOption(
                'target-user', null, InputOption::VALUE_REQUIRED, 'User name with access to the target DB.', 'root'
            ),
            // @todo make password secure
            new InputOption(
                'target-password', null, InputOption::VALUE_REQUIRED, 'Password for the target database.', ''
            ),
            new InputOption(
                'target-port', null, InputOption::VALUE_REQUIRED, 'Port of the target database..', '3306'
            ),
        ))
        ->setHelp(<<<EOT
Convert the Eve Toolkit MS-SQL database (fromDb) to a target db represented by the connection paramters (toDb).

EOT
        );
    }

	/**
	 * @param InputInterface $input
     * @param string $part (source or target)
	 *
	 * @return Connection
     * @throws DBALException
	 */
	private function createConnection(InputInterface $input, $part = 'source') {
	    return DriverManager::getConnection(array(
            'dbname' => $input->getOption($part . '-dbname'),
            'host' => $input->getOption($part . '-host'),
            'driver' => $input->getOption($part . '-driver'),
            'user' => $input->getOption($part . '-user'),
            'password' => $input->getOption($part . '-password'),
            'port' => $input->getOption($part . '-port'),
        ), new Configuration());
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function createMigration(InputInterface $input, OutputInterface $output) {
	    $success = true;

        try {
            $source = $this->createConnection($input, 'source');
        } catch (DBALException $e) {
            $output->write("Failed to create source connection: " . $e->getMessage(), true);
            $success = false;
        }

        try {
            $target = $this->createConnection($input, 'target');
        } catch (DBALException $e) {
            $output->write("Failed to create target connection: " . $e->getMessage(), true);
            $success = false;
        }

        if ($success) {
            $mapping = new Mapping();
            $chain = new ConverterChain($mapping);
            $chain->add(new DoctrineConverter());
            $chain->add(new EnsureAutoIncrementPrimaryKeyConverter());
            $chain->add(new RenamePrimaryKeyIfSingleColumnIndex());
            $this->migration = new Migration($source, $target, $chain);
        }

        return $success;
    }
}
