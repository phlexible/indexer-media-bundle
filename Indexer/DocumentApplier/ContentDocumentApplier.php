<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Content document applier.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ContentDocumentApplier implements DocumentApplierInterface
{
    /**
     * @var IndexibleVoterInterface
     */
    private $indexibleContentVoter;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param IndexibleVoterInterface  $indexibleContentVoter
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $logger
     */
    public function __construct(
        IndexibleVoterInterface $indexibleContentVoter,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->indexibleContentVoter = $indexibleContentVoter;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * @param DocumentInterface       $document
     * @param MediaDocumentDescriptor $descriptor
     */
    public function apply(DocumentInterface $document, MediaDocumentDescriptor $descriptor)
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
