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
use Phlexible\Bundle\IndexerBundle\Storage\Operation\Operations;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
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

        $this->storage->createOperations()->willReturn(new Operations());
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
        $this->assertTrue($this->indexer->supports(new DocumentIdentity('media_550e8400-e29b-11d4-a716-446655440000_1')));
    }

    public function testUnsupportedIdentifier()
    {
        $this->assertFalse($this->indexer->supports(new DocumentIdentity('test')));
    }

    public function testAdd()
    {
        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add(new DocumentIdentity('testIdentifier'));
    }

    public function testAddWithQueue()
    {
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->add(new DocumentIdentity('testIdentifier'), true);
    }

    public function testAddWithoutDocument()
    {
        $this->mapper->map('testIdentifier')->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add(new DocumentIdentity('testIdentifier'));
        $this->indexer->add(new DocumentIdentity('testIdentifier'), true);
    }

    public function testUpdate()
    {
        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update(new DocumentIdentity('testIdentifier'));
    }

    public function testUpdateWithQueue()
    {
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->update(new DocumentIdentity('testIdentifier'), true);
    }

    public function testUpdateWithoutDocument()
    {
        $this->mapper->map('testIdentifier')->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update(new DocumentIdentity('testIdentifier'));
        $this->indexer->update(new DocumentIdentity('testIdentifier'), true);
    }

    public function testDelete()
    {
        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete(new DocumentIdentity('testIdentifier'));
    }

    public function testDeleteWithQueue()
    {
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->delete(new DocumentIdentity('testIdentifier'), true);
    }

    public function testDeleteWithoutDocument()
    {
        $this->mapper->map('testIdentifier')->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete(new DocumentIdentity('testIdentifier'));
        $this->indexer->delete(new DocumentIdentity('testIdentifier'), true);
    }

    public function testIndexAll()
    {
        $this->mapper->findIdentifiers()->willReturn(array('file_550e8400-e29b-11d4-a716-446655440000_1', 'file_550e8400-e29b-11d4-a716-446655440001_2'));
        $this->mapper->map('file_550e8400-e29b-11d4-a716-446655440000_1')->willReturn(new MediaDocument());
        $this->mapper->map('file_550e8400-e29b-11d4-a716-446655440001_2')->willReturn(new MediaDocument());
        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->indexAll();
    }

    public function testIndexAllWithQueue()
    {
        $this->mapper->findIdentifiers()->willReturn(array('file_550e8400-e29b-11d4-a716-446655440000_1', 'file_550e8400-e29b-11d4-a716-446655440001_2'));
        $this->mapper->map('file_550e8400-e29b-11d4-a716-446655440000_1')->willReturn(new MediaDocument());
        $this->mapper->map('file_550e8400-e29b-11d4-a716-446655440001_2')->willReturn(new MediaDocument());
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->indexAll(true);
    }
}
