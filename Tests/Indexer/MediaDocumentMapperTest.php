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

use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\IndexerMediaBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier\DocumentApplierInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentMapper;
use Phlexible\Bundle\IndexerMediaBundle\IndexerMediaEvents;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Document mapper test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentMapper
 */
class MediaDocumentMapperTest extends TestCase
{
    use MediaDescriptorTrait;

    /**
     * @var IndexibleVoterInterface
     */
    private $voter;

    /**
     * @var DocumentApplierInterface
     */
    private $applier;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MediaDocument
     */
    private $document;

    /**
     * @var MediaDocumentDescriptor
     */
    private $descriptor;

    /**
     * @var MediaDocumentMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->voter = $this->prophesize(IndexibleVoterInterface::class);
        $this->applier = $this->prophesize(DocumentApplierInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->document = new MediaDocument();
        $this->descriptor = $this->createDescriptor();

        $this->mapper = new MediaDocumentMapper(
            $this->voter->reveal(),
            $this->applier->reveal(),
            $this->dispatcher->reveal(),
            $this->logger->reveal()
        );
    }

    public function testMapDocumentReturnsFalseOnNotIndexible()
    {
        $this->voter->isIndexible($this->descriptor)->willReturn(IndexibleVoterInterface::VOTE_DENY);

        $result = $this->mapper->mapDocument($this->document, $this->descriptor);

        $this->assertFalse($result);
    }

    public function testMapDocumentCallsApplier()
    {
        $this->applier->apply($this->document, $this->descriptor)->shouldBeCalled();

        $this->mapper->mapDocument($this->document, $this->descriptor);
    }

    public function testMapDocumentDispatchesMapDocumentEvent()
    {
        $this->dispatcher->dispatch(IndexerMediaEvents::MAP_DOCUMENT, Argument::type(MapDocumentEvent::class))->shouldBeCalled();

        $this->mapper->mapDocument($this->document, $this->descriptor);
    }

    public function testMapDocumentReturnsTrueOnSuccess()
    {
        $result = $this->mapper->mapDocument($this->document, $this->descriptor);

        $this->assertTrue($result);
    }
}
