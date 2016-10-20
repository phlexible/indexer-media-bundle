<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Delete command.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class DeleteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-media:delete')
            ->setDescription('Delete element document.')
            ->addArgument('fileId', InputArgument::REQUIRED, 'File ID')
            ->addArgument('fileVersion', InputArgument::REQUIRED, 'File Version')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);

        $fileId = $input->getArgument('fileId');
        $fileVersion = $input->getArgument('fileVersion');

        $indexer = $this->getContainer()->get('phlexible_indexer_media.media_indexer');
        $storage = $indexer->getStorage();

        $output->writeln('Indexer: '.$indexer->getName());
        $output->writeln('  Storage: '.get_class($storage));
        $output->writeln('    DSN: '.$storage->getConnectionString());

        $identifier = "file_{$fileId}_{$fileVersion}";

        $commands = $storage->createCommands();
        $commands->delete($identifier);

        $storage->runCommands($commands);

        return 0;
    }
}
