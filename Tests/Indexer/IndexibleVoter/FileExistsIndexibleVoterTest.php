<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Tests\Indexer\IndexibleVoter;

use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\FileExistsIndexibleVoter;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use PHPUnit\Framework\TestCase;

/**
 * File exists indexible voter.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\FileExistsIndexibleVoter
 */
class FileExistsIndexibleVoterTest extends TestCase
{
    use MediaDescriptorTrait;

    /**
     * @var FileExistsIndexibleVoter
     */
    private $voter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->voter = new FileExistsIndexibleVoter();
    }

    public function testVoteReturnsDenyOnNonExistingFile()
    {
        $descriptor = $this->createDescriptorForInvalidFile();

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(FileExistsIndexibleVoter::VOTE_DENY, $result);
    }

    public function testVoteReturnsAllowOnExistingFile()
    {
        $descriptor = $this->createDescriptor();

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(FileExistsIndexibleVoter::VOTE_ALLOW, $result);
    }
}
