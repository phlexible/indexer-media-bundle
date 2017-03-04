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
use Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier\BaseDocumentApplier;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base document applier test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\DocumentApplier\BaseDocumentApplier
 */
class BaseDocumentApplierTest extends TestCase
{
    use MediaDescriptorTrait;

    public function testApply()
    {
        $document = new MediaDocument();
        $descriptor = $this->createDescriptor();

        $applier = new BaseDocumentApplier(
            $this->prophesize(EventDispatcherInterface::class)->reveal(),
            $this->prophesize(LoggerInterface::class)->reveal()
        );
        $applier->apply($document, $descriptor);

        $this->assertSame($document->get('title'), 'testFile');
    }
}
