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

/**
 * Create document event
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class CreateDocumentEvent extends Event
{
    /**
     * @var string
     */
    protected $eventName = Events::CREATE_DOCUMENT;

    /**
     * @var DocumentInterface
     */
    protected $document = null;

    /**
     * @param DocumentInterface $document
     */
    public function __construct(DocumentInterface $document)
    {
        $this->document = $document;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }
}