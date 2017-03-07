<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Tests\Indexer\Mapper;

use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper\BaseDocumentMapper;
use Phlexible\Bundle\IndexerMediaBundle\Tests\MediaDescriptorTrait;
use PHPUnit\Framework\TestCase;

/**
 * Base document mapper test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper\BaseDocumentMapper
 */
class BaseDocumentMapperTest extends TestCase
{
    use MediaDescriptorTrait;

    public function testMapDocument()
    {
        $document = new MediaDocument();
        $descriptor = $this->createDescriptor();

        $applier = new BaseDocumentMapper();
        $applier->mapDocument($document, $descriptor);

        $this->assertSame($document->get('title'), 'testFile');
    }
}
