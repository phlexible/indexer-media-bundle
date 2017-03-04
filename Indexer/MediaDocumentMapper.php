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
use Phlexible\Bundle\IndexerMediaBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerMediaBundle\IndexerMediaEvents;
use Phlexible\Component\Formatter\FilesizeFormatter;
use Phlexible\Component\MediaExtractor\Extractor\ExtractorInterface;
use Phlexible\Component\MediaManager\Meta\FileMetaDataManager;
use Phlexible\Component\MediaManager\Meta\FileMetaSetResolver;
use Phlexible\Component\MediaType\Model\MediaTypeManagerInterface;
use Phlexible\Component\Volume\Model\FileInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Media indexer.
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaDocumentMapper
{
    /**
     * @var ExtractorInterface
     */
    private $extractor;

    /**
     * @var MediaTypeManagerInterface
     */
    private $mediaTypeManager;

    /**
     * @var FileMetaSetResolver
     */
    private $metasetResolver;

    /**
     * @var FileMetaDataManager
     */
    private $metaDataManager;

    /**
     * @var IndexibleVoterInterface
     */
    private $indexibleContentVoter;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $defaultLanguage;

    /**
     * @param ExtractorInterface        $extractor
     * @param MediaTypeManagerInterface $mediaTypeManager
     * @param FileMetaSetResolver       $metasetResolver
     * @param FileMetaDataManager       $metaDataManager
     * @param IndexibleVoterInterface   $indexibleContentVoter
     * @param EventDispatcherInterface  $dispatcher
     * @param string                    $defaultLanguage
     */
    public function __construct(
        ExtractorInterface $extractor,
        MediaTypeManagerInterface $mediaTypeManager,
        FileMetaSetResolver $metasetResolver,
        FileMetaDataManager $metaDataManager,
        IndexibleVoterInterface $indexibleContentVoter,
        EventDispatcherInterface $dispatcher,
        $defaultLanguage
    ) {
        $this->extractor = $extractor;
        $this->mediaTypeManager = $mediaTypeManager;
        $this->metasetResolver = $metasetResolver;
        $this->metaDataManager = $metaDataManager;
        $this->indexibleContentVoter = $indexibleContentVoter;
        $this->dispatcher = $dispatcher;
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * {@inheritdoc}
     */
    public function map(DocumentInterface $document, MediaDocumentDescriptor $descriptor)
    {
        $document = $this->applyDescriptor($document, $descriptor);

        return $document;
    }

    /**
     * @param DocumentInterface       $document
     * @param MediaDocumentDescriptor $descriptor
     *
     * @return DocumentInterface
     */
    private function applyDescriptor(DocumentInterface $document, MediaDocumentDescriptor $descriptor)
    {
        // TODO do we need boosting?

        // extract content
        //$content = $this->extractContent($file);

        // Field: readablefilesize
        $formatter = new FilesizeFormatter();
        $readableFileSize = $formatter->formatFilesize($descriptor->getFile()->getSize());

        // Field: url
        $url = '/download/'.$descriptor->getFile()->getId().'/'.$descriptor->getFile()->getName();

        // Field: Parent Folder IDs

        $parentFolderIds = array();
        $parentFolder = $descriptor->getFolder();

        while ($parentFolder) {
            $parentFolderIds[] = $parentFolder->getId();
            if (!$parentFolder->getParentId()) {
                break;
            }
            $parentFolder = $descriptor->getVolume()->findFolder($parentFolder->getParentId());
        }

        $document
            ->setIdentity($descriptor->getIdentity())
            ->set('title', $descriptor->getFile()->getName())
            ->set('folder_id', $descriptor->getFile()->getFolderID())
            ->set('parent_folder_ids', $parentFolderIds)
            ->set('file_id', $descriptor->getFile()->getID())
            ->set('file_version', $descriptor->getFile()->getVersion())
            ->set('filename', $descriptor->getFile()->getName())
            ->set('url', $url)
            ->set('mime_type', $descriptor->getFile()->getMimeType())
            ->set('media_category', $descriptor->getFile()->getMediaCategory())
            ->set('media_type', $descriptor->getFile()->getMediaType())
            ->set('filesize', $descriptor->getFile()->getSize())
            ->set('readable_filesize', $readableFileSize);

        if (IndexibleVoterInterface::VOTE_ALLOW === $this->indexibleContentVoter->isIndexible($descriptor)) {
            $content = base64_encode(file_get_contents($descriptor->getFile()->getPhysicalPath()));

            $document
                ->set('mediafile', array(
                    '_content_type' => $descriptor->getFile()->getMimeType(),
                    '_name' => $descriptor->getFile()->getName(),
                    '_content' => $content,
                ));
        }

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

        $event = new MapDocumentEvent($document, $descriptor);
        $this->dispatcher->dispatch(IndexerMediaEvents::MAP_DOCUMENT, $event);

        return $document;
    }

    /**
     * Extract content from asset.
     *
     * @param FileInterface $file
     *
     * @return string
     */
    private function extractContent(FileInterface $file)
    {
        // parse content
        $content = trim((string) $this->extractor->extract($file, $mediaType, null));

        if (!$content) {
            return null;
        }

        // Remove NL, CR, TABs
        $content = str_replace(array("\r", "\n", "\t"), ' ', $content);

        // Remove multiple whitespaces
        $content = preg_replace('/\s+/u', ' ', $content);

        // trim content
        $content = trim($content);

        return $content;
    }

    /**
     * @param FileInterface $file
     *
     * @return string
     */
    private function getMetaLanguage(FileInterface $file)
    {
        // use meta default language as fallback
        $metaLanguage = $this->defaultLanguage;

        $meta = $file->getMeta($metaLanguage);
        if (isset($meta['language']['value']) && strlen($meta['language']['value'])) {
            // use the language of the document for indexing meta informations
            $metaLanguage = $meta['language']['value'];
        }

        return $metaLanguage;
    }
}
