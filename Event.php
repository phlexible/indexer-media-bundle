<?php
/**
 * MWF - MAKEweb Framework
 *
 * PHP Version 5
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @copyright   2007 brainbits GmbH (http://www.brainbits.net)
 * @version     SVN: $Id: Generator.php 2312 2007-01-25 18:46:27Z swentz $
 */

/**
 * Media IndexerMedia Events
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @author      Stephan Wentz <sw@brainbits.net>
 * @copyright   2007 brainbits GmbH (http://www.brainbits.net)
 */
interface Media_IndexerMedia_Event
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
