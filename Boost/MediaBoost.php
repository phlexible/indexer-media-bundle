<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Boost;

use Phlexible\Bundle\IndexerBundle\Boost\AbstractBoost;

/**
 * Media boost
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class MediaBoost extends AbstractBoost
{
    public function __construct()
    {
        $this->addFieldBoost('copy', 1);
        $this->addFieldBoost('tags', 1.5);
        $this->addFieldBoost('title', 1.25);
        $this->addFieldPrecision('copy', 0.7);
        $this->addFieldPrecision('tags', 0.9);
        $this->addFieldPrecision('title', 0.8);
    }
}