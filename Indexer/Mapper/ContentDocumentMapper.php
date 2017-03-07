<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;

/**
 * Content document mapper.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ContentDocumentMapper implements MediaDocumentMapperInterface
{
    /**
     * @var IndexibleVoterInterface
     */
    private $indexibleContentVoter;

    /**
     * @param IndexibleVoterInterface  $indexibleContentVoter
     */
    public function __construct(IndexibleVoterInterface $indexibleContentVoter)
    {
        $this->indexibleContentVoter = $indexibleContentVoter;
    }

    public function mapDocument(DocumentInterface $document, MediaDocumentDescriptor $descriptor)
    {
        if (IndexibleVoterInterface::VOTE_ALLOW === $this->indexibleContentVoter->isIndexible($descriptor)) {
            $content = base64_encode(file_get_contents($descriptor->getFile()->getPhysicalPath()));

            $document
                ->set('mediafile', array(
                    '_content_type' => $descriptor->getFile()->getMimeType(),
                    '_name' => $descriptor->getFile()->getName(),
                    '_content' => $content,
                ));
        }
    }
}
