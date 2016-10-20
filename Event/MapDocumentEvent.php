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
use Phlexible\Component\Volume\Model\FileInterface;
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
     * @var FileInterface
     */
    private $file;

    /**
     * @param DocumentInterface $document
     * @param FileInterface     $file
     */
    public function __construct(DocumentInterface $document, FileInterface $file)
    {
        $this->document = $document;
        $this->file = $file;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return FileInterface
     */
    public function getFile()
    {
        return $this->file;
    }
}
