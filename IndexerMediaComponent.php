<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent;

use Phlexible\Component\AbstractComponent;

/**
 * Media indexer component
 *
 * @package Media_IndexerMedia
 */
class IndexerMediaComponent extends AbstractComponent
{
    public function __construct()
    {
        $this
            ->setVersion('0.7.0')
            ->setId('indexermedia')
            ->setPackage('phlexible');
    }
}
