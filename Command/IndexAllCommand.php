<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Phlexible\IndexerBundle\Storage\Storage;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index all command
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class IndexAllCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-media:index-all')
            ->setDescription('Index all media documents.')
            ->addOption('queue', null, InputOption::VALUE_NONE, 'Queue updates instead of immediate run.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $input->getOption('queue');

        ini_set('memory_limit', -1);

        $container = $this->getContainer();

        $indexer = $container->get('phlexible_indexer_media.indexer');
        $storage = $indexer->getStorage();

        $output->writeln('Indexer: ' . $indexer->getLabel());
        $output->writeln('Storage: ' . $storage->getLabel());

        $update = $storage->createUpdate();

        $documentIds = $indexer->getAllIdentifiers();

        $progress = new ProgressBar($output, count($documentIds));
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% %message%');
        $progress->start();

        foreach ($documentIds as $documentId) {
            $document = $indexer->getDocumentByIdentifier($documentId);

            //$output->writeln('Document: ' . $document->getDocumentType() . ' ' . $document->getDocumentClass() . ' ' . $document->getIdentifier());

            if ($queue) {

            } else {
                $update->addUpdate($document);
            }

            $progress->setMessage($document->getIdentifier());
            $progress->advance();
        }

        if ($queue) {

        } else {
            $update->addCommit();
        }

        $progress->finish();
        $output->writeln('');

        $storage->update($update);

        return 0;
    }

}
