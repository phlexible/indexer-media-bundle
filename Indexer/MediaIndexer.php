<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerBundle\Indexer\IndexerInterface;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Phlexible\Component\Formatter\FilesizeFormatter;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Media indexer
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
    public function supports($identifier)
    {
        return $identifier instanceof MediaDocument || preg_match('/^media_[0-9a-fA-F-]{36}_\d+$/', $identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function add($identifier, $viaQueue = false)
    {
        $document = $this->mapper->map($identifier);

        if (!$document) {
            return false;
        }

        $commands = $this->storage->createCommands()
            ->addDocument($document)
            ->commit();

        if (!$viaQueue) {
            $this->storage->runCommands($commands);
        } else {
            $this->storage->queueCommands($commands);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function update($identifier, $viaQueue = false)
    {
        $document = $this->mapper->map($identifier);

        if (!$document) {
            return false;
        }

        $commands = $this->storage->createCommands()
            ->updateDocument($document)
            ->commit();

        if (!$viaQueue) {
            $this->storage->runCommands($commands);
        } else {
            $this->storage->queueCommands($commands);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($identifier, $viaQueue = false)
    {
        $document = $this->mapper->map($identifier);

        if (!$document) {
            return false;
        }

        $commands = $this->storage->createCommands()
            ->deleteDocument($document)
            ->commit();

        if (!$viaQueue) {
            $this->storage->runCommands($commands);
        } else {
            $this->storage->queueCommands($commands);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll($viaQueue = false)
    {
        $documentIds = $this->mapper->findIdentifiers();

        $commands = $this->storage->createCommands();

        $cnt = 0;
        foreach ($documentIds as $documentId) {
            $document = $this->mapper->map($documentId);

            if (!$document) {
                $this->logger->error("Document $documentId could not be loaded.");
                continue;
            }

            $commands->addDocument($document);

            $cnt++;
        }

        $commands->commit();

        if (!$viaQueue) {
            $this->storage->runCommands($commands);
        } else {
            $this->storage->queueCommands($commands);
        }

        return $cnt;
    }
}
