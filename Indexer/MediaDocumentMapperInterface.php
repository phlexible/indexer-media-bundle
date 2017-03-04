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

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;

/**
 * Media document mapper interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface MediaDocumentMapperInterface
{
    /**
     * @param DocumentInterface       $document
     * @param MediaDocumentDescriptor $descriptor
     *
     * @return bool
     */
    public function mapDocument(DocumentInterface $document, MediaDocumentDescriptor $descriptor);
}
