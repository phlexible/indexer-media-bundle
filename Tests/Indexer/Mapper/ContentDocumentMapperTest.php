<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Tests\Indexer\Mapper;

use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper\ContentDocumentMapper;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use PHPUnit\Framework\TestCase;

/**
 * Content document mapper test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper\ContentDocumentMapper
 */
class ContentDocumentMapperTest extends TestCase
{
    use MediaDescriptorTrait;

    /**
     * @var IndexibleVoterInterface
     */
    private $contentIndexibleVoter;

    /**
     * @var ContentDocumentMapper
     */
    private $applier;

    public function setUp()
    {
        $this->contentIndexibleVoter = $this->prophesize(IndexibleVoterInterface::class);

        $this->applier = new ContentDocumentMapper($this->contentIndexibleVoter->reveal());
    }

    public function testMapDocumentIsCalledForVoterAllow()
    {
        $document = new MediaDocument();
        $descriptor = $this->createDescriptor();

        $this->contentIndexibleVoter->isIndexible($descriptor)->willReturn(IndexibleVoterInterface::VOTE_ALLOW);

        $this->applier->mapDocument($document, $descriptor);

        $this->assertNotEmpty($document->get('mediafile'));
    }

    public function testMapDocumentIsCalledForVoterDeny()
    {
        $document = new MediaDocument();
        $descriptor = $this->createDescriptor();

        $this->contentIndexibleVoter->isIndexible($descriptor)->willReturn(IndexibleVoterInterface::VOTE_DENY);

        $this->applier->mapDocument($document, $descriptor);

        $this->assertEmpty($document->get('mediafile'));
    }
}
