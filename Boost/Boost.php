<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent\Boost;

use Phlexible\IndexerComponent\Boost\AbstractBoost;

/**
 * Media search boost
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class Boost extends AbstractBoost
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