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
 * Media Search Boost
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @author      Marco Fischer <mf@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Media_IndexerMedia_Boost extends MWF_Core_Indexer_Boost_Abstract
{
    protected $_customBoosts = array(
        'copy'  => 1,
        'tags'  => 1.5,
        'title' => 1.25
    );

    protected $_customPrecision = array(
        'copy'  => 0.7,
        'tags'  => 0.9,
        'title' => 0.8
    );
}