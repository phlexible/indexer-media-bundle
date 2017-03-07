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

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaIndexerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add command.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class AddCommand extends Command
{
    private $indexer;

    public function __construct(MediaIndexerInterface $indexer)
    {
        parent::__construct();

        $this->indexer = $indexer;
    }

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

        $storage = $this->indexer->getStorage();

        $output->writeln('Indexer: '.get_class($this->indexer));
        $output->writeln('  Storage: '.get_class($storage));
        $output->writeln('    DSN: '.$storage->getConnectionString());

        $identifier = new DocumentIdentity("media_{$fileId}_{$fileVersion}");

        if (!$this->indexer->add($identifier)) {
            $output->writeln("<error>Document $identifier could not be loaded.</error>");

            return 1;
        }

        $output->writeln("$identifier index done.");

        return 0;
    }
}
