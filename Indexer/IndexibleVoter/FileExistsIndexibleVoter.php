<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter;

use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;

/**
 * File exists indexible voter.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class FileExistsIndexibleVoter implements IndexibleVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function isIndexible(MediaDocumentDescriptor $descriptor)
    {
        if (file_exists($descriptor->getFile()->getPhysicalPath())) {
            return self::VOTE_ALLOW;
        }

        return self::VOTE_DENY;
    }
}
