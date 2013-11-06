<?php
/**
 * Phlexible
 *
 * PHP Version 5
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */

/**
 * Frontend Fulltext Search Query
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @author      Marco Fischer <mf@brainbits.net>
 * @author      Phillip Look <pl@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Media_IndexerMedia_Query extends MWF_Core_Indexer_Query_Abstract
{
    protected $_fields        = array('title', 'tags', 'copy');
    protected $_documentTypes = array('media');
    protected $_label         = 'Media search';
}