<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Tests\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaContentIdentifier;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;
use Phlexible\Bundle\MediaManagerBundle\Entity\File;
use Phlexible\Bundle\MediaManagerBundle\Entity\Folder;
use Phlexible\Component\Volume\Model\FileInterface;
use Phlexible\Component\Volume\Model\FolderInterface;
use Phlexible\Component\Volume\Volume;
use Phlexible\Component\Volume\VolumeManager;
use PHPUnit\Framework\TestCase;

/**
 * Media content identifier test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\ContentIdentifier
 */
class MediaContentIdentifierTest extends TestCase
{
    /**
     * @var MediaContentIdentifier
     */
    private $identifier;

    /**
     * @var VolumeManager
     */
    private $volumeManager;

    public function setUp()
    {
        $this->volumeManager = $this->prophesize(VolumeManager::class);
        $voter = $this->prophesize(IndexibleVoterInterface::class);

        $this->identifier = new MediaContentIdentifier(
            $this->volumeManager->reveal(),
            $voter->reveal()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateValidIdentifierReturnsTrue()
    {
        $this->assertTrue($this->identifier->validateIdentity(new DocumentIdentity('media_2C265ABC-ABB2-4AC4-8004-85562394C343_1')));
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateInvalidIdentifierReturnsFalse()
    {
        $this->assertFalse($this->identifier->validateIdentity(new DocumentIdentity('invalid_2C265ABC-ABB2-4AC4-8004-85562394C343_1')));
        $this->assertFalse($this->identifier->validateIdentity(new DocumentIdentity('media_2C265ABC-AB2-4AC4-8004-85562394C343_1')));
        $this->assertFalse($this->identifier->validateIdentity(new DocumentIdentity('media_2C265ABC-ABB2-4AC4-8004-85562394C343_x')));
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateIdentityFromFile()
    {
        $volume = $this->prophesize(Volume::class);
        $folder = $this->prophesize(FolderInterface::class);
        $file = $this->prophesize(FileInterface::class);
        $file->getId()->willReturn('foo');
        $file->getVersion()->willReturn(1);
        $file->getFolder()->willReturn($folder->reveal());
        $file->getVolume()->willReturn($volume->reveal());

        $descriptor = $this->identifier->createDescriptorFromFile($file->reveal());

        $this->assertInstanceOf(MediaDocumentDescriptor::class, $descriptor);
        $this->assertSame($file->reveal(), $descriptor->getFile());
        $this->assertSame($folder->reveal(), $descriptor->getFolder());
        $this->assertSame($volume->reveal(), $descriptor->getVolume());
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateIdentityFromIdentifier()
    {
        $volume = $this->prophesize(Volume::class);
        $folder = $this->prophesize(FolderInterface::class);
        $file = $this->prophesize(FileInterface::class);
        $file->getFolder()->willReturn($folder->reveal());
        $file->getVolume()->willReturn($volume->reveal());

        $this->volumeManager->findByFileId('2C265ABC-ABB2-4AC4-8004-85562394C343')->willReturn($volume->reveal());
        $volume->findFile('2C265ABC-ABB2-4AC4-8004-85562394C343', 2)->willReturn($file->reveal());

        $identity = new DocumentIdentity('media_2C265ABC-ABB2-4AC4-8004-85562394C343_2');
        $descriptor = $this->identifier->createDescriptorFromIdentity($identity);

        $this->assertInstanceOf(MediaDocumentDescriptor::class, $descriptor);
        $this->assertSame($identity, $descriptor->getIdentity());
        $this->assertSame($file->reveal(), $descriptor->getFile());
        $this->assertSame($folder->reveal(), $descriptor->getFolder());
        $this->assertSame($volume->reveal(), $descriptor->getVolume());
    }
}
