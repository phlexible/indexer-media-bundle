<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent\Event;

use Phlexible\IndexerComponent\Document\DocumentInterface;
use Phlexible\MediaSiteComponent\File\FileInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Map document event
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MapDocumentEvent extends Event
{
    /**
     * @var DocumentInterface
     */
    private $document = null;

    /**
     * @var FileInterface
     */
    private $file = null;

    /**
     * @param DocumentInterface $document
     * @param FileInterface     $file
     */
    public function __construct(DocumentInterface $document, FileInterface $file)
    {
        $this->document = $document;
        $this->file     = $file;
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