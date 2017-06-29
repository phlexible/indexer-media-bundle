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
use Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper\ChainDocumentMapper;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper\MediaDocumentMapperInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Chain document mapper test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper\ChainDocumentMapper
 */
class ChainDocumentMapperTest extends TestCase
{
    use MediaDescriptorTrait;

    /**
     * @var MediaDocumentMapperInterface
     */
    private $mapper1;

    /**
     * @var MediaDocumentMapperInterface
     */
    private $mapper2;

    /**
     * @var IndexibleVoterInterface
     */
    private $voter;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var MediaDocument
     */
    private $document;

    /**
     * @var MediaDocumentDescriptor
     */
    private $descriptor;

    /**
     * @var ChainDocumentMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->mapper1 = $this->prophesize(MediaDocumentMapperInterface::class);
        $this->mapper2 = $this->prophesize(MediaDocumentMapperInterface::class);

        $this->document = new MediaDocument();
        $this->descriptor = $this->createDescriptor();

        $this->mapper = new ChainDocumentMapper(array($this->mapper1->reveal(), $this->mapper2->reveal()));
    }

    public function testMapDocumentCallsMappers()
    {
        $this->mapper1->mapDocument($this->document, $this->descriptor)->shouldBeCalled();
        $this->mapper2->mapDocument($this->document, $this->descriptor)->shouldBeCalled();

        $this->mapper->mapDocument($this->document, $this->descriptor);
    }
}
