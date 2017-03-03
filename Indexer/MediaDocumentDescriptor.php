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
use Phlexible\Component\Volume\Model\FileInterface;
use Phlexible\Component\Volume\Model\FolderInterface;
use Phlexible\Component\Volume\VolumeInterface;

/**
 * Media document descriptor.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MediaDocumentDescriptor
{
    /**
     * @var DocumentIdentity
     */
    private $identity;

    /**
     * @var VolumeInterface
     */
    private $volume;

    /**
     * @var FileInterface
     */
    private $file;

    /**
     * @var FolderInterface
     */
    private $folder;

    /**
     * @param DocumentIdentity $identity
     * @param VolumeInterface  $volume
     * @param FileInterface    $file
     * @param FolderInterface  $folder
     */
    public function __construct(DocumentIdentity $identity, VolumeInterface $volume, FileInterface $file, FolderInterface $folder)
    {
        $this->identity = $identity;
        $this->volume = $volume;
        $this->file = $file;
        $this->folder = $folder;
    }

    /**
     * @return DocumentIdentity
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return VolumeInterface
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @return FileInterface
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return FolderInterface
     */
    public function getFolder()
    {
        return $this->folder;
    }
}
