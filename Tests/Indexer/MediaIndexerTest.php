<?php

namespace Phlexible\Bundle\IndexerMediaBundle\Tests\Indexer;

use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerBundle\Storage\UpdateQuery\Command\CommandCollection;
use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentMapper;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaIndexer;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class MediaIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MediaIndexer
     */
    private $indexer;

    /**
     * @var StorageInterface|ObjectProphecy
     */
    private $storage;

    /**
     * @var MediaDocumentMapper|ObjectProphecy
     */
    private $mapper;

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
        $this->storage = $this->prophesize('Phlexible\Bundle\IndexerBundle\Storage\StorageInterface');
        $this->mapper = $this->prophesize('Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentMapper');
        $this->jobManager = $this->prophesize('Phlexible\Bundle\QueueBundle\Model\JobManagerInterface');
        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');

        $this->storage->createCommands()->willReturn(new CommandCollection());
        $this->mapper->map('testIdentifier')->willReturn(new MediaDocument());

        $this->indexer = new MediaIndexer(
            $this->storage->reveal(),
            $this->mapper->reveal(),
            $this->jobManager->reveal(),
            $this->logger->reveal()
        );
    }

    public function testSupportedIdentifier()
    {
        $this->assertTrue($this->indexer->supports('media_550e8400-e29b-11d4-a716-446655440000_1'));
    }

    public function testUnsupportedIdentifier()
    {
        $this->assertFalse($this->indexer->supports('test'));
    }

    public function testAdd()
    {
        $this->storage->runCommands(Argument::cetera())->shouldBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add('testIdentifier');
    }

    public function testAddWithQueue()
    {
        $this->storage->runCommands(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldBeCalled();

        $this->indexer->add('testIdentifier', true);
    }

    public function testAddWithoutDocument()
    {
        $this->mapper->map('testIdentifier')->willReturn(null);
        $this->storage->runCommands(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add('testIdentifier');
        $this->indexer->add('testIdentifier', true);
    }

    public function testUpdate()
    {
        $this->storage->runCommands(Argument::cetera())->shouldBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update('testIdentifier');
    }

    public function testUpdateWithQueue()
    {
        $this->storage->runCommands(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldBeCalled();

        $this->indexer->update('testIdentifier', true);
    }

    public function testUpdateWithoutDocument()
    {
        $this->mapper->map('testIdentifier')->willReturn(null);
        $this->storage->runCommands(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update('testIdentifier');
        $this->indexer->update('testIdentifier', true);
    }

    public function testDelete()
    {
        $this->storage->runCommands(Argument::cetera())->shouldBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete('testIdentifier');
    }

    public function testDeleteWithQueue()
    {
        $this->storage->runCommands(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldBeCalled();

        $this->indexer->delete('testIdentifier', true);
    }

    public function testDeleteWithoutDocument()
    {
        $this->mapper->map('testIdentifier')->willReturn(null);
        $this->storage->runCommands(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete('testIdentifier');
        $this->indexer->delete('testIdentifier', true);
    }

    public function testIndexAll()
    {
        $this->mapper->findIdentifiers()->willReturn(array('file_550e8400-e29b-11d4-a716-446655440000_1', 'file_550e8400-e29b-11d4-a716-446655440001_2'));
        $this->mapper->map('file_550e8400-e29b-11d4-a716-446655440000_1')->willReturn(new MediaDocument());
        $this->mapper->map('file_550e8400-e29b-11d4-a716-446655440001_2')->willReturn(new MediaDocument());
        $this->storage->runCommands(Argument::cetera())->shouldBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->indexAll();
    }

    public function testIndexAllWithQueue()
    {
        $this->mapper->findIdentifiers()->willReturn(array('file_550e8400-e29b-11d4-a716-446655440000_1', 'file_550e8400-e29b-11d4-a716-446655440001_2'));
        $this->mapper->map('file_550e8400-e29b-11d4-a716-446655440000_1')->willReturn(new MediaDocument());
        $this->mapper->map('file_550e8400-e29b-11d4-a716-446655440001_2')->willReturn(new MediaDocument());
        $this->storage->runCommands(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queueCommands(Argument::cetera())->shouldBeCalled();

        $this->indexer->indexAll(true);
    }
}
