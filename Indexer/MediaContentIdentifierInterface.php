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

use Generator;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Component\Volume\Model\FileInterface;

/**
 * Media content identifier interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface MediaContentIdentifierInterface
{
    /**
     * @param DocumentIdentity $identity
     *
     * @return bool
     */
    public function validateIdentity(DocumentIdentity $identity);

    /**
     * @param FileInterface $file
     *
     * @return MediaDocumentDescriptor
     */
    public function createDescriptorFromFile(FileInterface $file);

    /**
     * @param DocumentIdentity $identity
     *
     * @return MediaDocumentDescriptor
     */
    public function createDescriptorFromIdentity(DocumentIdentity $identity);

    /**
     * Return all identifiers.
     *
     * @return Generator|MediaDocumentDescriptor[]
     */
    public function findAllDescriptors();
}
