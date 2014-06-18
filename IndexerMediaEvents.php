<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaBundle;

/**
 * Media indexer events
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface IndexerMediaEvents
{
    /**
     * Map Document Event
     * Fired when a document is mapped
     */
    const MAP_DOCUMENT = 'phlexible_indexer_media.map_document';
}
