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
use Phlexible\Component\Volume\Model\FileInterface;
use Phlexible\Component\Volume\Model\FolderInterface;
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
        $volume = $this->prophesize(VolumeInterface::class);
        $volume->getId()->willReturn('abc');
        $volume->getRootDir()->willReturn(__DIR__);
        $folder = $this->prophesize(FolderInterface::class);
        $file = $this->prophesize(FileInterface::class);
        $file->getId()->willReturn('foo');
        $file->getVersion()->willReturn(1);
        $file->getName()->willReturn('testFile');
        $file->getPhysicalPath()->willReturn(__FILE__);
        $file->getHash()->willReturn(basename(__FILE__));
        $file->getVolume()->willReturn($volume->reveal());
        $file->getFolder()->willReturn($folder->reveal());
        $file->getMimeType()->willReturn('text/plain');
        $file->getSize()->willReturn(1000);
        $file->getFolderId()->willReturn('def');

        return new MediaDocumentDescriptor(
            new DocumentIdentity('abc'),
            $volume->reveal(),
            $file->reveal(),
            $folder->reveal()
        );
    }

    /**
     * @return MediaDocumentDescriptor
     */
    protected function createDescriptorForInvalidFile()
    {
        $volume = $this->prophesize(VolumeInterface::class);
        $volume->getId()->willReturn('abc');
        $volume->getRootDir()->willReturn('/invalid__dir');
        $folder = $this->prophesize(FolderInterface::class);
        $file = $this->prophesize(FileInterface::class);
        $file->getId()->willReturn('bar');
        $file->getVersion()->willReturn(2);
        $file->getName()->willReturn('invalid__file');
        $file->getPhysicalPath()->willReturn('/invalid__dir/invalid__file');
        $file->getHash()->willReturn('invalid__hash');
        $file->getVolume()->willReturn($volume->reveal());
        $file->getFolder()->willReturn($folder->reveal());
        $file->getMimeType()->willReturn('text/plain');
        $file->getSize()->willReturn(999);
        $file->getFolderId()->willReturn('def');

        return new MediaDocumentDescriptor(
            new DocumentIdentity('abc'),
            $volume->reveal(),
            $file->reveal(),
            $folder->reveal()
        );
    }
}
