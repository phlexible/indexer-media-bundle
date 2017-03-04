<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Tests;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;
use Phlexible\Bundle\MediaManagerBundle\Entity\File;
use Phlexible\Bundle\MediaManagerBundle\Entity\Folder;
use Phlexible\Component\Volume\VolumeInterface;

/**
 * Media descriptor trait.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
trait MediaDescriptorTrait
{
    /**
     * @return MediaDocumentDescriptor
     */
    protected function createDescriptor()
    {
        $folder = new Folder();
        $volume = $this->prophesize(VolumeInterface::class);
        $volume->getId()->willReturn('abc');
        $volume->getRootDir()->willReturn(__DIR__);
        $file = new File();
        $file->setName('testFile');
        $file->setHash(basename(__FILE__));
        $file->setVolume($volume->reveal());
        $file->setFolder($folder);

        return new MediaDocumentDescriptor(new DocumentIdentity('abc'), $volume->reveal(), $file, $folder);
    }

    /**
     * @return MediaDocumentDescriptor
     */
    protected function createDescriptorForInvalidFile()
    {
        $folder = new Folder();
        $volume = $this->prophesize(VolumeInterface::class);
        $volume->getId()->willReturn('abc');
        $volume->getRootDir()->willReturn('/invalid__dir');
        $file = new File();
        $file->setName('invalidFile');
        $file->setHash('invalid__file');
        $file->setVolume($volume->reveal());
        $file->setFolder($folder);

        return new MediaDocumentDescriptor(new DocumentIdentity('abc'), $volume->reveal(), $file, $folder);
    }
}
