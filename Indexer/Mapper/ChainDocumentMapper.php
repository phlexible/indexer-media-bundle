<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;

/**
 * Chain media document mapper.
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class ChainDocumentMapper implements MediaDocumentMapperInterface
{
    /**
     * @var MediaDocumentMapperInterface[]
     */
    private $mappers = array();

    /**
     * @param MediaDocumentMapperInterface[] $mappers
     */
    public function __construct(array $mappers)
    {
        foreach ($mappers as $mapper) {
            $this->addMapper($mapper);
        }
    }

    /**
     * @param MediaDocumentMapperInterface $mapper
     */
    private function addMapper(MediaDocumentMapperInterface $mapper)
    {
        $this->mappers[] = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDocument(DocumentInterface $document, MediaDocumentDescriptor $descriptor)
    {
        foreach ($this->mappers as $mapper) {
            $mapper->mapDocument($document, $descriptor);
        }
    }
}
