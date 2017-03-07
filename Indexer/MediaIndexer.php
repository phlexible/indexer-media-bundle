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
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Phlexible\Component\Volume\Model\FileInterface;
use Psr\Log\LoggerInterface;

/**
 * Media indexer.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaIndexer implements MediaIndexerInterface
{
    /**
     * @var MediaDocumentBuilder
     */
    private $builder;

    /**
     * @var StorageInterface
     */
    private $storage;

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
     * @var int
     */
    private $batchSize;

    /**
     * @param MediaDocumentBuilder            $builder
     * @param StorageInterface                $storage
     * @param MediaContentIdentifierInterface $identifier
     * @param JobManagerInterface             $jobManager
     * @param LoggerInterface                 $logger
     * @param int                             $batchSize
     */
    public function __construct(
        MediaDocumentBuilder $builder,
        StorageInterface $storage,
        MediaContentIdentifierInterface $identifier,
        JobManagerInterface $jobManager,
        LoggerInterface $logger,
        $batchSize = 10
    ) {
        $this->builder = $builder;
        $this->storage = $storage;
        $this->identifier = $identifier;
        $this->jobManager = $jobManager;
        $this->logger = $logger;
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
        if (!($descriptor = $this->identifier->createDescriptorFromIdentity($identity))) {
            return;
        }

        if (!($document = $this->builder->build($descriptor))) {
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
        if (!($document = $this->builder->build($descriptor))) {
            return;
        }

        $method .= 'Document';

        $operations = $this->storage->createOperations()
            ->$method($document)
            ->commit();

        $this->storage->execute($operations);
    }

    /**
     * @param FileInterface $file
     * @param bool          $viaQueue
     *
     * @return bool
     */
    public function addFile(FileInterface $file, $viaQueue = false)
    {
        $this->logger->debug("addFile {$file->getId()} {$file->getVersion()}");

        $descriptor = $this->identifier->createDescriptorFromFile($file);

        if ($viaQueue) {
            $this->queueDescriptorOperation('add', $descriptor);
        } else {
            $this->executeDescriptorOperation('add', $descriptor);
        }

        return 1;
    }

    /**
     * @param FileInterface $file
     * @param bool          $viaQueue
     *
     * @return bool
     */
    public function updateFile(FileInterface $file, $viaQueue = false)
    {
        $this->logger->debug("updateFile {$file->getId()} {$file->getVersion()}");

        $descriptor = $this->identifier->createDescriptorFromFile($file);

        if ($viaQueue) {
            $this->queueDescriptorOperation('update', $descriptor);
        } else {
            $this->executeDescriptorOperation('update', $descriptor);
        }

        return 1;
    }

    /**
     * @param FileInterface $file
     * @param bool          $viaQueue
     *
     * @return bool
     */
    public function deleteFile(FileInterface $file, $viaQueue = false)
    {
        $this->logger->debug("deleteFile {$file->getId()} {$file->getVersion()}");

        $descriptor = $this->identifier->createDescriptorFromFile($file);

        if ($viaQueue) {
            $this->queueDescriptorOperation('delete', $descriptor);
        } else {
            $this->executeDescriptorOperation('delete', $descriptor);
        }

        return 1;
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

            if (!($document = $this->builder->build($descriptor))) {
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
}
