<?php

/*
 * This file is part of the phlexible indexer mage package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Tests\Indexer\IndexibleVoter;

use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\ChainIndexibleVoter;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use PHPUnit\Framework\TestCase;

/**
 * Chain indexible voter.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\ChainIndexibleVoter
 */
class ChainIndexibleVoterTest extends TestCase
{
    use MediaDescriptorTrait;

    /**
     * @var MediaDocumentDescriptor
     */
    private $descriptor;

    public function setUp()
    {
        $this->descriptor = $this->createDescriptor();
    }

    public function testIndexibleChainReturnsAllowOnAllAllowed()
    {
        $voter1 = $this->prophesize(IndexibleVoterInterface::class);
        $voter2 = $this->prophesize(IndexibleVoterInterface::class);

        $voter1->isIndexible($this->descriptor)->willReturn(ChainIndexibleVoter::VOTE_ALLOW);
        $voter2->isIndexible($this->descriptor)->willReturn(ChainIndexibleVoter::VOTE_ALLOW);

        $voter = new ChainIndexibleVoter(array($voter1->reveal(), $voter2->reveal()));
        $result = $voter->isIndexible($this->descriptor);

        $this->assertSame(ChainIndexibleVoter::VOTE_ALLOW, $result);
    }

    public function testIndexibleChainReturnsDenyOnSingleDeny()
    {
        $voter1 = $this->prophesize(IndexibleVoterInterface::class);
        $voter2 = $this->prophesize(IndexibleVoterInterface::class);

        $voter1->isIndexible($this->descriptor)->willReturn(ChainIndexibleVoter::VOTE_ALLOW);
        $voter2->isIndexible($this->descriptor)->willReturn(ChainIndexibleVoter::VOTE_DENY);

        $voter = new ChainIndexibleVoter(array($voter1->reveal(), $voter2->reveal()));
        $result = $voter->isIndexible($this->descriptor);

        $this->assertSame(ChainIndexibleVoter::VOTE_DENY, $result);
    }

    public function testIndexibleChainReturnsDenyOnFirstDeny()
    {
        $voter1 = $this->prophesize(IndexibleVoterInterface::class);
        $voter2 = $this->prophesize(IndexibleVoterInterface::class);

        $voter1->isIndexible($this->descriptor)->willReturn(ChainIndexibleVoter::VOTE_DENY);
        $voter2->isIndexible($this->descriptor)->shouldNotBeCalled();

        $voter = new ChainIndexibleVoter(array($voter1->reveal(), $voter2->reveal()));
        $result = $voter->isIndexible($this->descriptor);

        $this->assertSame(ChainIndexibleVoter::VOTE_DENY, $result);
    }
}
