<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Event;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;
use Symfony\Component\EventDispatcher\Event;

/**
 * Map document event.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MapDocumentEvent extends Event
{
    /**
     * @var DocumentInterface
     */
    private $document;

    /**
     * @var MediaDocumentDescriptor
     */
    private $descriptor;

    /**
     * @param DocumentInterface       $document
     * @param MediaDocumentDescriptor $descriptor
     */
    public function __construct(DocumentInterface $document, MediaDocumentDescriptor $descriptor)
    {
        $this->document = $document;
        $this->descriptor = $descriptor;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return MediaDocumentDescriptor
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }
}
