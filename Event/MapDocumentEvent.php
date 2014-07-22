<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Event;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\MediaSiteBundle\Model\FileInterface;
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