<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Indexer\IndexerInterface;
use Phlexible\Component\Volume\Model\FileInterface;

/**
 * Media indexer interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface MediaIndexerInterface extends IndexerInterface
{
    /**
     * @param FileInterface $file
     * @param bool          $viaQueue
     *
     * @return bool
     */
    public function addFile(FileInterface $file, $viaQueue = false);

    /**
     * @param FileInterface $file
     * @param bool          $viaQueue
     *
     * @return bool
     */
    public function updateFile(FileInterface $file, $viaQueue = false);

    /**
     * @param FileInterface $file
     * @param bool          $viaQueue
     *
     * @return bool
     */
    public function deleteFile(FileInterface $file, $viaQueue = false);
}
