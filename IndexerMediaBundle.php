<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent;

use Phlexible\Component\Component;

/**
 * Media indexer bundle
 *
 * @package Media_IndexerMedia
 */
class IndexerMediaBundle extends Component
{
    public function __construct()
    {
        $this
            ->setVersion('0.7.0')
            ->setId('indexermedia')
            ->setPackage('phlexible');
    }
}
