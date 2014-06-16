<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaBundle\Event;

use Phlexible\IndexerBundle\Document\DocumentInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Create document event
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class CreateDocumentEvent extends Event
{
    /**
     * @var DocumentInterface
     */
    private $document = null;

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