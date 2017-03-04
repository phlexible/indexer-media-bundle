<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Tests\Indexer\DocumentApplier;

use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier\ContentDocumentApplier;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Content document applier test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier\ContentDocumentApplier
 */
class ContentDocumentApplierTest extends TestCase
{
    use MediaDescriptorTrait;

    /**
     * @var IndexibleVoterInterface
     */
    private $contentIndexibleVoter;

    /**
     * @var ContentDocumentApplier
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

    public function setUp()
    {
        $this->contentIndexibleVoter = $this->prophesize(IndexibleVoterInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->applier = new ContentDocumentApplier(
            $this->contentIndexibleVoter->reveal(),
            $this->dispatcher->reveal(),
            $this->logger->reveal()
        );
    }

    public function testApplyIsCalledForVoterAllow()
    {
        $document = new MediaDocument();
        $descriptor = $this->createDescriptor();

        $this->contentIndexibleVoter->isIndexible($descriptor)->willReturn(IndexibleVoterInterface::VOTE_ALLOW);

        $this->applier->apply($document, $descriptor);

        $this->assertNotEmpty($document->get('mediafile'));
    }

    public function testApplyIsCalledForVoterDeny()
    {
        $document = new MediaDocument();
        $descriptor = $this->createDescriptor();

        $this->contentIndexibleVoter->isIndexible($descriptor)->willReturn(IndexibleVoterInterface::VOTE_DENY);

        $this->applier->apply($document, $descriptor);

        $this->assertEmpty($document->get('mediafile'));
    }
}
