<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerMediaBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier\DocumentApplierInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\IndexerMediaEvents;
use Phlexible\Component\Volume\Model\FileInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Media document mapper.
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaDocumentMapper implements MediaDocumentMapperInterface
{
    /**
     * @var IndexibleVoterInterface
     */
    private $indexibleVoter;

    /**
     * @var DocumentApplierInterface
     */
    private $applier;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param IndexibleVoterInterface  $indexibleVoter
     * @param DocumentApplierInterface $applier
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $logger
     */
    public function __construct(
        IndexibleVoterInterface $indexibleVoter,
        DocumentApplierInterface $applier,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->indexibleVoter = $indexibleVoter;
        $this->applier = $applier;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDocument(DocumentInterface $document, MediaDocumentDescriptor $descriptor)
    {
        if (IndexibleVoterInterface::VOTE_DENY === $this->indexibleVoter->isIndexible($descriptor)) {
            return false;
        }

        try {
            $this->applier->apply($document, $descriptor);
        } catch (\Exception $e) {
            $this->logger->error('mapIdentity() exception: '.$e->getMessage());

            throw $e;
        }

        $event = new MapDocumentEvent($document, $descriptor);
        $this->dispatcher->dispatch(IndexerMediaEvents::MAP_DOCUMENT, $event);

        return true;
    }
}
