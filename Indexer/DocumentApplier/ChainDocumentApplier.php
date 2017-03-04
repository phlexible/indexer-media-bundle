<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;

/**
 * Chain document applier.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ChainDocumentApplier implements DocumentApplierInterface
{
    /**
     * @var DocumentApplierInterface[]
     */
    private $appliers;

    /**
     * @param DocumentApplierInterface[] $appliers
     */
    public function __construct(array $appliers)
    {
        $this->appliers = $appliers;
    }

    /**
     * @param DocumentInterface       $document
     * @param MediaDocumentDescriptor $descriptor
     */
    public function apply(DocumentInterface $document, MediaDocumentDescriptor $descriptor)
    {
        foreach ($this->appliers as $applier) {
            $applier->apply($document, $descriptor);
        }
    }
}
