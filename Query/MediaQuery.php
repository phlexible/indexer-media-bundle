<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaBundle\Query;

use Phlexible\IndexerBundle\Query\AbstractQuery;

/**
 * Media query
 *
 * @author Marco Fischer <mf@brainbits.net>
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaQuery extends AbstractQuery
{
    protected $_fields        = array('title', 'tags', 'copy');
    protected $_documentTypes = array('media');
    protected $_label         = 'Media search';
}