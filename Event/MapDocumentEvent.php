<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent\Event;

use Phlexible\Event\Event;
use Phlexible\IndexerComponent\Document\DocumentInterface;
use Phlexible\IndexerMediaComponent\Events;
use Phlexible\MediaSiteComponent\File\FileInterface;

/**
 * Map document event
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MapDocumentEvent extends Event
{
    /**
     * @var string
     */
    protected $eventName = Events::MAP_DOCUMENT;

    /**
     * @var DocumentInterface
     */
    protected $document = null;

    /**
     * @var FileInterface
     */
    protected $file = null;

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