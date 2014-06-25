<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Phlexible\Bundle\IndexerBundle\Storage\Storage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index command
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class IndexCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-media:index')
            ->setDescription('Index media document.')
            ->addOption('documentId', 'd', InputOption::VALUE_REQUIRED, 'Document ID')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $documentId = $input->getOption('documentId');

        ini_set('memory_limit', -1);

        $container = $this->getContainer();

        $indexer = $container->get('phlexible_indexer_media.indexer');

        $output->writeln('Indexer: ' . $indexer->getLabel());

        /* @var $storage Storage */
        $storage = $indexer->getStorage();
        $update = $storage->createUpdate();
        $document = $indexer->getDocumentByIdentifier($documentId);

        $output->writeln('Document: ' . $document->getDocumentType() . ' ' . $document->getDocumentClass() . ' ' . $document->getIdentifier());

        $update->addUpdate($document);
        $update->addCommit();

        $storage->update($update);

        return 0;
    }

}
