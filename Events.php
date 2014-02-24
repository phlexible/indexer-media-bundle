<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent;

/**
 * Media indexer events
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface Events
{
    /**
     * Create Document Event
     * Fired when a new document is created
     */
    const CREATE_DOCUMENT = 'indexermedia.create_document';

    /**
     * Map Document Event
     * Fired when a document is mapped
     */
    const MAP_DOCUMENT = 'indexermedia.map_document';
}
