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
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Component\Volume\Model\FileInterface;
use Phlexible\Component\Volume\Model\FolderInterface;
use Phlexible\Component\Volume\VolumeInterface;
use Phlexible\Component\Volume\VolumeManager;

/**
 * Media content identifier.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MediaContentIdentifier implements MediaContentIdentifierInterface
{
    /**
     * @var VolumeManager
     */
    private $volumeManager;

    /**
     * @var IndexibleVoterInterface
     */
    private $indexibleVoter;

    /**
     * @param VolumeManager           $volumeManager
     * @param IndexibleVoterInterface $indexibleVoter
     */
    public function __construct(
        VolumeManager $volumeManager,
        IndexibleVoterInterface $indexibleVoter
    ) {
        $this->volumeManager = $volumeManager;
        $this->indexibleVoter = $indexibleVoter;
    }

    /**
     * {@inheritdoc}
     */
    public function validateIdentity(DocumentIdentity $identity)
    {
        return (bool) $this->matchIdentity($identity);
    }

    /**
     * {@inheritdoc}
     */
    public function createDescriptorFromFile(FileInterface $file)
    {
        $volume = $file->getVolume();

        if (!$volume) {
            return null;
        }

        $folder = $file->getFolder();

        if (!$folder) {
            return null;
        }

        return new MediaDocumentDescriptor($this->createIdentity($file), $volume, $file, $folder);
    }

    /**
     * {@inheritdoc}
     */
    public function createDescriptorFromIdentity(DocumentIdentity $identity)
    {
        $match = $this->matchIdentity($identity);
        if (!$match) {
            return null;
        }

        $fileId = $match[1];
        $fileVersion = $match[2];

        $volume = $this->volumeManager->findByFileId($fileId);
        if (!$volume) {
            return null;
        }

        $file = $volume->findFile($fileId, $fileVersion);
        if (!$file) {
            return null;
        }

        $folder = $file->getFolder();
        if (!$folder) {
            return null;
        }

        return new MediaDocumentDescriptor($identity, $volume, $file, $folder);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllDescriptors()
    {
        foreach ($this->volumeManager->all() as $volume) {
            /* @var $volume VolumeInterface */

            $rii = new \RecursiveIteratorIterator($volume->getIterator(), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($rii as $folder) {
                /* @var $folder FolderInterface */

                $files = $volume->findFilesByFolder($folder);

                foreach ($files as $file) {
                    /* @var $file FileInterface */

                    $descriptor = new MediaDocumentDescriptor(
                        $this->createIdentity($file),
                        $volume,
                        $file,
                        $folder
                    );

                    if ($this->indexibleVoter->isIndexible($descriptor) === IndexibleVoterInterface::VOTE_DENY) {
                        continue;
                    }

                    yield $descriptor;
                }
            }
        }
    }

    /**
     * @param FileInterface $file
     *
     * @return DocumentIdentity
     */
    protected function createIdentity(FileInterface $file)
    {
        return new DocumentIdentity("media_{$file->getId()}_{$file->getVersion()}");
    }

    /**
     * @param DocumentIdentity $identity
     *
     * @return array|null
     */
    protected function matchIdentity(DocumentIdentity $identity)
    {
        if (!preg_match('/^media_([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12})_(\d+)$/', $identity->getIdentifier(), $match)) {
            return null;
        }

        return $match;
    }
}
