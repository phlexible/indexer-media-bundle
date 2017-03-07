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

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\IndexerMediaBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper\MediaDocumentMapperInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentBuilder;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;
use Phlexible\Bundle\IndexerMediaBundle\IndexerMediaEvents;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Media document builder test.
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentBuilder
 */
class MediaDocumentBuilderTest extends TestCase
{
    use MediaDescriptorTrait;

    /**
     * @var MediaDocument
     */
    private $document;

    /**
     * @var MediaDocumentDescriptor
     */
    private $descriptor;

    /**
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @var MediaDocumentMapperInterface|ObjectProphecy
     */
    private $mapper;

    /**
     * @var MediaDocumentBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->document = new MediaDocument();
        $this->document->setIdentity(new DocumentIdentity('A'));

        $this->descriptor = $this->createDescriptor();

        $this->documentFactory = $this->prophesize(DocumentFactory::class);
        $this->documentFactory->factory(MediaDocument::class)->willReturn($this->document);
        $this->mapper = $this->prophesize(MediaDocumentMapperInterface::class);
        $this->voter = $this->prophesize(IndexibleVoterInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->builder = new MediaDocumentBuilder(
            $this->documentFactory->reveal(),
            $this->mapper->reveal(),
            $this->voter->reveal(),
            $this->dispatcher->reveal()
        );
    }

    public function testBuildReturnsOnDeny()
    {
        $this->voter->isIndexible($this->descriptor)->willReturn(IndexibleVoterInterface::VOTE_DENY);
        $this->mapper->mapDocument($this->document)->shouldNotBeCalled();

        $this->builder->build($this->descriptor);
    }

    public function testBuildCallsMapperOnAllow()
    {
        $this->voter->isIndexible($this->descriptor)->willReturn(IndexibleVoterInterface::VOTE_ALLOW);
        $this->mapper->mapDocument($this->document, $this->descriptor)->shouldBeCalled();

        $this->builder->build($this->descriptor);
    }

    public function testMapDocumentDispatchesMapDocumentEvent()
    {
        $this->voter->isIndexible($this->descriptor)->willReturn(IndexibleVoterInterface::VOTE_ALLOW);

        $this->dispatcher->dispatch(IndexerMediaEvents::MAP_DOCUMENT, Argument::type(MapDocumentEvent::class))->shouldBeCalled();

        $this->builder->build($this->descriptor);
    }
}
