<?php

namespace App\Command;

use Doctrine\DBAL\DBALException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTargetSchemaCommand extends AbstractSchemaMigrationCommand
{
    protected function configure()
    {
        parent::configure();

		$this
        ->setName('neoen:eve:toolkit:createTargetSchema')
        ->setDescription('Creates target schema on the target db.')
        ->setHelp(<<<EOT
Creates target schema on the target db.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
	{
        if (!$this->createMigration($input, $output)) {
            return;
        }

        try {
            $this->migration->applyChanges();
        }
        catch (DBALException $e) {
            $output->write("Target schema could not be created: " . $e->getMessage(), true);
            return;
        }

        $output->write("Target schema created", true);
	}
}
