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
    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return array('title', 'tags', 'copy');
    }
    /**
     * {@inheritdoc}
     */
    public function getDocumentType()
    {
        return array('media');
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Media search';
    }
}