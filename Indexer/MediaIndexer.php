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

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerBundle\Indexer\IndexerInterface;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Media indexer.
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaIndexer implements IndexerInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var MediaDocumentMapper
     */
    private $mapper;

    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StorageInterface    $storage
     * @param MediaDocumentMapper $mapper
     * @param JobManagerInterface $jobManager
     * @param LoggerInterface     $logger
     */
    public function __construct(
        StorageInterface $storage,
        MediaDocumentMapper $mapper,
        JobManagerInterface $jobManager,
        LoggerInterface $logger)
    {
        $this->storage = $storage;
        $this->mapper = $mapper;
        $this->jobManager = $jobManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Media indexer';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'media';
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentClass()
    {
        return $this->mapper->getDocumentClass();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DocumentIdentity $identity)
    {
        return (bool) preg_match('/^media_[0-9a-fA-F-]{36}_\d+$/', (string) $identity);
    }

    /**
     * {@inheritdoc}
     */
    public function add(DocumentIdentity $identity, $viaQueue = false)
    {
        $document = $this->mapper->map($identity);

        if (!$document) {
            return false;
        }

        $operations = $this->storage->createOperations()
            ->addDocument($document)
            ->commit();

        if (!$viaQueue) {
            $this->storage->execute($operations);
        } else {
            $this->storage->queue($operations);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function update(DocumentIdentity $identity, $viaQueue = false)
    {
        $document = $this->mapper->map($identity);

        if (!$document) {
            return false;
        }

        $operations = $this->storage->createOperations()
            ->updateDocument($document)
            ->commit();

        if (!$viaQueue) {
            $this->storage->execute($operations);
        } else {
            $this->storage->queue($operations);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(DocumentIdentity $identity, $viaQueue = false)
    {
        $document = $this->mapper->map($identity);

        if (!$document) {
            return false;
        }

        $operations = $this->storage->createOperations()
            ->deleteDocument($document)
            ->commit();

        if (!$viaQueue) {
            $this->storage->execute($operations);
        } else {
            $this->storage->queue($operations);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll($viaQueue = false)
    {
        $documentIds = $this->mapper->findIdentifiers();

        $operations = $this->storage->createOperations();

        $cnt = 0;
        foreach ($documentIds as $documentId) {
            $document = $this->mapper->map($documentId);

            if (!$document) {
                $this->logger->error("Document $documentId could not be loaded.");
                continue;
            }

            $operations->addDocument($document);

            ++$cnt;
        }

        $operations->commit();

        if (!$viaQueue) {
            $this->storage->execute($operations);
        } else {
            $this->storage->queue($operations);
        }

        return $cnt;
    }

    /**
     * @return DocumentInterface
     */
    public function createDocument()
    {
        $class = $this->mapper->getDocumentClass();

        return new $class();
    }
}
