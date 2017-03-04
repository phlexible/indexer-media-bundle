<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Tests\Indexer\DocumentApplier;

use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier\ChainDocumentApplier;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier\DocumentApplierInterface;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use PHPUnit\Framework\TestCase;

/**
 * Chain document applier test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier\ChainDocumentApplier
 */
class ChainDocumentApplierTest extends TestCase
{
    use MediaDescriptorTrait;

    public function testApplier()
    {
        $document = new MediaDocument();
        $descriptor = $this->createDescriptor();

        $applier1 = $this->prophesize(DocumentApplierInterface::class);
        $applier2 = $this->prophesize(DocumentApplierInterface::class);

        $applier1->apply($document, $descriptor)->shouldBeCalled();
        $applier2->apply($document, $descriptor)->shouldBeCalled();

        $applier = new ChainDocumentApplier(array($applier1->reveal(), $applier2->reveal()));
        $applier->apply($document, $descriptor);
    }
}
