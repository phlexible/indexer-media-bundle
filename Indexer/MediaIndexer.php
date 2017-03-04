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
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerBundle\Indexer\IndexerInterface;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
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
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var MediaDocumentMapper
     */
    private $mapper;

    /**
     * @var MediaContentIdentifierInterface
     */
    private $identifier;

    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param DocumentFactory                 $documentFactory
     * @param StorageInterface                $storage
     * @param MediaDocumentMapper             $mapper
     * @param MediaContentIdentifierInterface $identifier
     * @param JobManagerInterface             $jobManager
     * @param LoggerInterface                 $logger
     * @param string                          $documentClass
     * @param int                             $batchSize
     */
    public function __construct(
        DocumentFactory $documentFactory,
        StorageInterface $storage,
        MediaDocumentMapper $mapper,
        MediaContentIdentifierInterface $identifier,
        JobManagerInterface $jobManager,
        LoggerInterface $logger,
        $documentClass = MediaDocument::class,
        $batchSize = 10
    ) {
        $this->documentFactory = $documentFactory;
        $this->storage = $storage;
        $this->mapper = $mapper;
        $this->identifier = $identifier;
        $this->jobManager = $jobManager;
        $this->logger = $logger;
        $this->documentClass = $documentClass;
        $this->batchSize = $batchSize;
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
    public function supports(DocumentIdentity $identity)
    {
        return $this->identifier->validateIdentity($identity);
    }

    /**
     * @param string           $method
     * @param DocumentIdentity $identity
     */
    private function queueIdentityOperation($method, DocumentIdentity $identity)
    {
        $method .= 'Identity';

        $operations = $this->storage->createOperations()
            ->$method($identity)
            ->commit();

        $this->storage->queue($operations);
    }

    /**
     * @param string                  $method
     * @param MediaDocumentDescriptor $descriptor
     */
    private function queueDescriptorOperation($method, MediaDocumentDescriptor $descriptor)
    {
        $method .= 'Identity';

        $operations = $this->storage->createOperations()
            ->$method($descriptor->getIdentity())
            ->commit();

        $this->storage->queue($operations);
    }

    /**
     * @param string           $method
     * @param DocumentIdentity $identity
     */
    private function executeIdentityOperation($method, DocumentIdentity $identity)
    {
        $descriptor = $this->identifier->createDescriptorFromIdentity($identity);
        if (!$descriptor) {
            return;
        }

        $document = $this->createDocument();
        if (!$this->mapper->map($document, $descriptor)) {
            return;
        }

        $method .= 'Document';

        $operations = $this->storage->createOperations()
            ->$method($document)
            ->commit();

        $this->storage->execute($operations);
    }

    /**
     * @param string                  $method
     * @param MediaDocumentDescriptor $descriptor
     */
    private function executeDescriptorOperation($method, MediaDocumentDescriptor $descriptor)
    {
        $document = $this->createDocument();
        if (!$this->mapper->map($document, $descriptor)) {
            return;
        }

        $method .= 'Document';

        $operations = $this->storage->createOperations()
            ->$method($document)
            ->commit();

        $this->storage->execute($operations);
    }

    /**
     * {@inheritdoc}
     */
    public function add(DocumentIdentity $identity, $viaQueue = false)
    {
        $this->logger->debug("add {$identity}");

        if ($viaQueue) {
            $this->queueIdentityOperation('add', $identity);
        } else {
            $this->executeIdentityOperation('add', $identity);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function update(DocumentIdentity $identity, $viaQueue = false)
    {
        $this->logger->debug("update {$identity}");

        if ($viaQueue) {
            $this->queueIdentityOperation('update', $identity);
        } else {
            $this->executeIdentityOperation('update', $identity);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(DocumentIdentity $identity, $viaQueue = false)
    {
        if ($viaQueue) {
            $this->queueIdentityOperation('delete', $identity);
        } else {
            $this->executeIdentityOperation('delete', $identity);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll()
    {
        $descriptors = $this->identifier->findAllDescriptors();

        $handled = 0;
        $batch = 0;

        $operations = $this->storage->createOperations();

        foreach ($descriptors as $descriptor) {
            ++$handled;

            $this->logger->info("indexAll add {$descriptor->getFile()->getId()} {$descriptor->getFile()->getVersion()}");

            $document = $this->createDocument();
            if (!$this->mapper->map($document, $descriptor)) {
                $this->logger->warning("indexAll skipping {$descriptor->getFile()->getId()} {$descriptor->getFile()->getVersion()}");
                continue;
            }
            $operations->addDocument($document);

            ++$batch;

            if ($batch % $this->batchSize === 0) {
                $this->logger->notice("indexAll batch commit ($handled)");

                $operations->commit();

                $this->storage->execute($operations);

                $operations = $this->storage->createOperations();
            }
        }

        if (count($operations)) {
            $this->logger->notice("indexAll commit ($handled)");
            $operations->commit();

            $this->storage->execute($operations);
        }

        return $handled;
    }

    /**
     * {@inheritdoc}
     */
    public function queueAll()
    {
        $descriptors = $this->identifier->findAllDescriptors();

        $handled = 0;
        $batch = 0;
        $total = count($descriptors);

        $operations = $this->storage->createOperations();

        foreach ($descriptors as $descriptor) {
            ++$handled;

            $this->logger->info("queueAll add {$descriptor->getFile()->getId()} {$descriptor->getFile()->getVersion()}");

            $operations->addIdentity($descriptor->getIdentity());

            ++$batch;

            if ($batch % $this->batchSize === 0) {
                $this->logger->notice("queueAll batch commit ($handled/$total)");

                $operations->commit();

                $this->storage->queue($operations);

                $operations = $this->storage->createOperations();
            }
        }

        if (count($operations)) {
            $this->logger->notice("queueAll commit ($handled/$total)");

            $operations->commit();

            $this->storage->queue($operations);
        }

        return $handled;
    }

    /**
     * @return DocumentInterface
     */
    public function createDocument()
    {
        return $this->documentFactory->factory($this->documentClass);
    }
}
