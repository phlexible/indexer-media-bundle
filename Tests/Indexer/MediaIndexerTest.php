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
use Phlexible\Bundle\IndexerBundle\Storage\Operation\Operations;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper\MediaDocumentMapperInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaContentIdentifierInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentBuilder;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaIndexer;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * Media indexer test.
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaIndexer
 */
class MediaIndexerTest extends TestCase
{
    use MediaDescriptorTrait;

    /**
     * @var MediaDocument
     */
    private $document;

    /**
     * @var MediaDocumentBuilder
     */
    private $builder;

    /**
     * @var MediaIndexer
     */
    private $indexer;

    /**
     * @var StorageInterface|ObjectProphecy
     */
    private $storage;

    /**
     * @var MediaContentIdentifierInterface|ObjectProphecy
     */
    private $identifier;

    /**
     * @var JobManagerInterface|ObjectProphecy
     */
    private $jobManager;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    public function setUp()
    {
        $this->document = new MediaDocument();
        $this->document->setIdentity(new DocumentIdentity('A'));

        $this->builder = $this->prophesize(MediaDocumentBuilder::class);
        $this->storage = $this->prophesize(StorageInterface::class);
        $this->identifier = $this->prophesize(MediaContentIdentifierInterface::class);
        $this->identifier->willImplement(MediaContentIdentifierInterface::class);
        $this->jobManager = $this->prophesize(JobManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->storage->createOperations()->willReturn(new Operations());

        $this->indexer = new MediaIndexer(
            $this->builder->reveal(),
            $this->storage->reveal(),
            $this->identifier->reveal(),
            $this->jobManager->reveal(),
            $this->logger->reveal()
        );
    }

    public function testSupportedIdentifier()
    {
        $identity = new DocumentIdentity('media_550e8400-e29b-11d4-a716-446655440000_1');

        $this->identifier->validateIdentity($identity)->willReturn(true);

        $this->assertTrue($this->indexer->supports($identity));
    }

    public function testUnsupportedIdentifier()
    {
        $identity = new DocumentIdentity('media_550e8400-e29b-11d4-a716-446655440000_1');

        $this->identifier->validateIdentity($identity)->willReturn(false);

        $this->assertFalse($this->indexer->supports($identity));
    }

    public function testAdd()
    {
        $identity = new DocumentIdentity('media_74_1');
        $descriptor = $this->createDescriptor();

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->builder->build($descriptor)->shouldBeCalled()->willReturn($this->document);

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add($identity);
    }

    public function testAddWithQueue()
    {
        $identity = new DocumentIdentity('media_74_1');

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->add($identity, true);
    }

    public function testAddWithoutDocument()
    {
        $identity = new DocumentIdentity('media_74_1');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add($identity);
    }

    public function testUpdate()
    {
        $identity = new DocumentIdentity('media_74_1');
        $descriptor = $this->createDescriptor();

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->builder->build($descriptor)->shouldBeCalled()->willReturn($this->document);

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update($identity);
    }

    public function testUpdateWithQueue()
    {
        $identity = new DocumentIdentity('media_74_1');

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->update($identity, true);
    }

    public function testUpdateWithoutDocument()
    {
        $identity = new DocumentIdentity('media_74_1');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update($identity);
    }

    public function testDelete()
    {
        $identity = new DocumentIdentity('media_74_1');
        $descriptor = $this->createDescriptor();

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->builder->build($descriptor)->shouldBeCalled()->willReturn($this->document);

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete($identity);
    }

    public function testDeleteWithQueue()
    {
        $identity = new DocumentIdentity('media_74_1');

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->delete($identity, true);
    }

    public function testDeleteWithoutDocument()
    {
        $identity = new DocumentIdentity('media_74_1');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete($identity);
    }

    public function testIndexAll()
    {
        $descriptor1 = $this->createDescriptor();
        $descriptor2 = $this->createDescriptor();

        $this->identifier->findAllDescriptors()->willReturn(array($descriptor1, $descriptor2));

        $this->builder->build($descriptor1)->shouldBeCalled()->willReturn($this->document);
        $this->builder->build($descriptor2)->shouldBeCalled()->willReturn($this->document);

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->indexAll();
    }

    public function testQueueAll()
    {
        $descriptor1 = $this->createDescriptor();
        $descriptor2 = $this->createDescriptor();

        $this->identifier->findAllDescriptors()->willReturn(array($descriptor1, $descriptor2));

        $this->builder->build($descriptor1)->shouldNotBeCalled();
        $this->builder->build($descriptor2)->shouldNotBeCalled();

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->queueAll();
    }
}
