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

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\IndexerMediaBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper\MediaDocumentMapperInterface;
use Phlexible\Bundle\IndexerMediaBundle\IndexerMediaEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Media document builder.
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaDocumentBuilder
{
    /**
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @var MediaDocumentMapperInterface
     */
    private $mapper;

    /**
     * @var IndexibleVoterInterface
     */
    private $indexibleVoter;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @param DocumentFactory              $documentFactory
     * @param MediaDocumentMapperInterface $mapper
     * @param IndexibleVoterInterface      $indexibleVoter
     * @param EventDispatcherInterface     $dispatcher
     * @param string                       $documentClass
     */
    public function __construct(
        DocumentFactory $documentFactory,
        MediaDocumentMapperInterface $mapper,
        IndexibleVoterInterface $indexibleVoter,
        EventDispatcherInterface $dispatcher,
        $documentClass = MediaDocument::class
    ) {
        $this->documentFactory = $documentFactory;
        $this->mapper = $mapper;
        $this->indexibleVoter = $indexibleVoter;
        $this->dispatcher = $dispatcher;
        $this->documentClass = $documentClass;
    }

    /**
     * @param MediaDocumentDescriptor $descriptor
     *
     * @return null|MediaDocument
     */
    public function build(MediaDocumentDescriptor $descriptor)
    {
        if (IndexibleVoterInterface::VOTE_DENY === $this->indexibleVoter->isIndexible($descriptor)) {
            return null;
        }

        $document = $this->createDocument();

        $this->mapper->mapDocument($document, $descriptor);

        $event = new MapDocumentEvent($document, $descriptor);
        $this->dispatcher->dispatch(IndexerMediaEvents::MAP_DOCUMENT, $event);

        return $document;
    }

    /**
     * @return MediaDocument
     */
    public function createDocument()
    {
        return $this->documentFactory->factory($this->documentClass);
    }
}
