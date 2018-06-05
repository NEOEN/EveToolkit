<?php

namespace App\Command;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use SchumannIt\DBAL\Schema\Converter\ConverterChain;
use SchumannIt\DBAL\Schema\Converter\DoctrineConverter;
use SchumannIt\DBAL\Schema\Converter\EnsureAutoIncrementPrimaryKeyConverter;
use SchumannIt\DBAL\Schema\Converter\RenamePrimaryKeyIfSingleColumnIndex;
use SchumannIt\DBAL\Schema\Mapping;
use SchumannIt\DBAL\Schema\Migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SdeMigrationCommand extends Command
{
    const MODES = [
        'show-schema-changes',
        'apply-schema-changes',
        'show-data-diff',
        'sync-data',
    ];

    /**
     * @var Migration
     */
    private $migration;
    /**
     * @var Connection
     */
    private $targetConnection;

    public function __construct(string $name = null, Connection $connection)
    {
        $this->targetConnection = $connection;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('neoen:eve:toolkit:migration')
            ->setDescription('Apply source db changes to current target schema and/or show changes sql.')
            ->setHelp(<<<EOT
Apply source db changes to current target schema and/or show changes sql.
EOT
            );

        // migration options
        $this->setDefinition([
            new InputArgument(
                'mode', InputArgument::REQUIRED, 'What to do. Available modes are: ' . implode(', ', self::MODES)
            ),
            new InputOption(
                'tables', null, InputOption::VALUE_REQUIRED, 'Comma separated list of original table names for data migration. (sync-data).'
            ),
            new InputOption(
                'force', null, InputOption::VALUE_NONE, 'Forces show-data-diff and sync-data to process tables even if tables seem in sync. BE CAREFULL!!, All previous data will be lost while syncing.'
            ),
            new InputOption(
                'only-table-names', null, InputOption::VALUE_NONE, 'Will only show table names for show-data-diff.'
            )
        ]);

        $this->configureConnectionParameters();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
	{
	    if (!$this->createMigration($input, $output)) {
            $output->write('Failed to create migration.', true);
            return;
        }

        switch ($input->getArgument('mode')) {
            case 'show-schema-changes':
                foreach ($this->migration->getChangesSql() as $line) {
                    $output->write($line . ';', true);
                }
                return;

            case 'apply-schema-changes':
                try {
                    $this->migration->applyChanges();
                } catch (DBALException $e) {
                    $output->write("Failed to apply changes to the target db: " . $e->getMessage(), true);
                }
                return;

            case 'show-data-diff':
                $this->processDataDiff($input, $output);
                return;

            case 'sync-data':
                $this->processSyncData($input, $output);
                return;
        }
	}

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function processDataDiff(InputInterface $input, OutputInterface $output): void
    {
        try {
            $diff = $this->migration->compareRecordCount($input->getOption('force'));
        } catch (DBALException $e) {
            $output->write("Failed to fetch diff: ".$e->getMessage(), true);
            return;
        }

        if ($input->getOption('only-table-names')) {
            foreach (array_keys($diff) as $table) {
                $output->write($table, true);
            }
        } else {
            foreach ($diff as $originalName => $data) {
                $output->write($originalName.': '.$data['originalCount'].' = '.$data['targetCount'].': '.$data['targetTableName'], true);
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function processSyncData(InputInterface $input, OutputInterface $output): void
    {
        $tables = [];
        if ($input->getOption('tables')) {
            $tablesOption = explode(',', $input->getOption('tables'));
            if (!is_array($tablesOption)) {
                $output->write("--tables input does not seem to be a comma separated list.");
                return;
            }
            foreach ($tablesOption as $table) {
                $tables[] = trim($table);
            }
        }

        try {
            $this->migration->migrateData($tables, $input->getOption('force'));
        } catch (DBALException $e) {
            $output->write("Failed to migrate data (some data might have been written): ".$e->getMessage(), true);
        }
    }

    /**
     * @param InputInterface $input
     * @param string $part (source or target)
     *
     * @return Connection
     * @throws DBALException
     */
    private function createConnectionFromInput(InputInterface $input, $part = 'source') {
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
    private function createMigration(InputInterface $input, OutputInterface $output) {
        $success = true;

        try {
            $source = $this->createConnectionFromInput($input, 'source');
            $source->connect();
        } catch (DBALException $e) {
            $output->write("Failed to create source connection: " . $e->getMessage(), true);
            $success = false;
        }

        try {
            if (is_null($this->targetConnection)) {
                $this->targetConnection = $this->createConnectionFromInput($input, 'target');
            }
            $this->targetConnection->connect();
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
            $this->migration = new Migration($source, $this->targetConnection, $chain);
        }

        return $success;
    }

    private function configureConnectionParameters(): void
    {
        // if we have no target connection yet, we add options to create one
        if (is_null($this->targetConnection)) {
            $this->getDefinition()->addOptions(
                [
                    new InputOption(
                        'target-host', null, InputOption::VALUE_REQUIRED, 'Host of the target database".', 'localhost'
                    ),
                    new InputOption(
                        'target-driver', null, InputOption::VALUE_REQUIRED, 'Db type of the target db.', 'pdo_mysql'
                    ),
                    new InputOption(
                        'target-dbname', null, InputOption::VALUE_REQUIRED, 'DB name of the target database.', 'sde'
                    ),
                    new InputOption(
                        'target-user',
                        null,
                        InputOption::VALUE_REQUIRED,
                        'User name with access to the target DB.',
                        'root'
                    ),
                    // @todo make password secure
                    new InputOption(
                        'target-password', null, InputOption::VALUE_REQUIRED, 'Password for the target database.', ''
                    ),
                    new InputOption(
                        'target-port', null, InputOption::VALUE_REQUIRED, 'Port of the target database..', '3306'
                    )
                ]
            );
        }

        $defaultHost = 'localhost';
        $defaultDriver = 'pdo_mysql';
        $defaultUser = 'evetoolkit';
        $defaultPassword = 'master';
        $defaultPort = '3306';
        $defaultDbName = 'sde';

        if (!is_null($this->targetConnection)) {
            $defaultHost = $this->targetConnection->getHost();
            $defaultDriver = $this->targetConnection->getDriver()->getName();
            $defaultUser = $this->targetConnection->getUsername();
            $defaultPassword = $this->targetConnection->getPassword();
            $defaultPort = $this->targetConnection->getPort();
        }

        // source connection options
        $this->getDefinition()->addOptions(
            [
                new InputOption(
                    'source-host',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Host of the source  database in the form".',
                    $defaultHost
                ),
                new InputOption(
                    'source-driver', null, InputOption::VALUE_REQUIRED, 'The driver.', $defaultDriver
                ),
                new InputOption(
                    'source-dbname', null, InputOption::VALUE_REQUIRED, 'DB name of the source', $defaultDbName
                ),
                new InputOption(
                    'source-user',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'User name with access to the source database.',
                    $defaultUser
                ),
                // @todo make password secure
                new InputOption(
                    'source-password',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Password for the source database.',
                    $defaultPassword
                ),
                new InputOption(
                    'source-port', null, InputOption::VALUE_REQUIRED, 'Port of the source database..', $defaultPort
                ),
            ]
        );
    }
}
