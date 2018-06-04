<?php

namespace App\Command;

use Doctrine\DBAL\DBALException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CopyDataCommand extends AbstractSchemaMigrationCommand
{
    protected function configure()
    {
        parent::configure();

        $this->getDefinition()->addOptions(array(
            new InputOption(
                'show-changes', null, InputOption::VALUE_NONE, 'Display migration sql.'
            ),
            new InputOption(
                'apply', null, InputOption::VALUE_NONE, 'Apply changes to target db.'
            )
        ));

		$this
        ->setName('neoen:eve:toolkit:copyData')
        ->setDescription('Apply source db changes to current target schema and/or show changes sql.')
        ->setHelp(<<<EOT
Apply source db changes to current target schema and/or show changes sql.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
	{
	    if (!$this->createMigration($input, $output)) {
	        return;
        }

        if ($input->getOption('show-changes')) {
            $output->write($this->migration->getChangesSql(), true);
        }

        if ($input->getOption('apply')) {
            try {
                $output->write("Writing changes to the target DB", true);
                $this->migration->applyChanges();
            } catch (DBALException $e) {
                $output->write("Failed to apply changes to the target db: " . $e->getMessage(), true);
            }
        }
	}
}
