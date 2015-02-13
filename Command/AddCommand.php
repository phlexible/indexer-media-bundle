<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add command
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class AddCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-media:add')
            ->setDescription('Index media document.')
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

        $output->writeln('Indexer: ' . $indexer->getName());
        $output->writeln('  Storage: ' . get_class($storage));
        $output->writeln('    DSN: ' . $storage->getConnectionString());

        $identifier = "media_{$fileId}_{$fileVersion}";

        if (!$indexer->add($identifier)) {
            $output->writeln("<error>Document $identifier could not be loaded.</error>");

            return 1;
        }

        $output->writeln("$identifier index done.");

        return 0;
    }

}
