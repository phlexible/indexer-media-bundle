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
use Phlexible\Component\MediaManager\Meta\FileMetaDataManager;
use Phlexible\Component\MediaManager\Meta\FileMetaSetResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Meta document applier.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MetaDocumentApplier implements DocumentApplierInterface
{
    /**
     * @var FileMetaSetResolver
     */
    private $metasetResolver;

    /**
     * @var FileMetaDataManager
     */
    private $metaDataManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param FileMetaSetResolver      $metasetResolver
     * @param FileMetaDataManager      $metaDataManager
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $logger
     */
    public function __construct(
        FileMetaSetResolver $metasetResolver,
        FileMetaDataManager $metaDataManager,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->metasetResolver = $metasetResolver;
        $this->metaDataManager = $metaDataManager;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * @param DocumentInterface       $document
     * @param MediaDocumentDescriptor $descriptor
     */
    public function apply(DocumentInterface $document, MediaDocumentDescriptor $descriptor)
    {
        $metasets = $this->metasetResolver->resolve($descriptor->getFile());
        $metasetNames = array();
        $metaData = array();
        foreach ($metasets as $metaset) {
            $metasetNames[] = $metaset->getName();
            $metadata = $this->metaDataManager->findByMetaSetAndFile($metaset, $descriptor->getFile());

            if (!$metadata) {
                continue;
            }

            $values = $metadata->getValues();

            foreach ($values as $language => $languageValues) {
                foreach ($languageValues as $languageKey => $languageValue) {
                    $metaData[] = $languageValue;
                }
            }
        }

        $document->set('metasets', $metasetNames);
        $document->set('tags', $metaData);

    }
}
