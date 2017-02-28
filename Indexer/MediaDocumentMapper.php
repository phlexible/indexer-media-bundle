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

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument;
use Phlexible\Bundle\IndexerMediaBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerMediaBundle\IndexerMediaEvents;
use Phlexible\Component\Formatter\FilesizeFormatter;
use Phlexible\Component\MediaExtractor\Extractor\ExtractorInterface;
use Phlexible\Component\MediaManager\Meta\FileMetaDataManager;
use Phlexible\Component\MediaManager\Meta\FileMetaSetResolver;
use Phlexible\Component\MediaType\Model\MediaTypeManagerInterface;
use Phlexible\Component\Volume\Model\FileInterface;
use Phlexible\Component\Volume\Model\FolderInterface;
use Phlexible\Component\Volume\VolumeInterface;
use Phlexible\Component\Volume\VolumeManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Media indexer.
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaDocumentMapper
{
    /**
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @var ExtractorInterface
     */
    private $extractor;

    /**
     * @var VolumeManager
     */
    private $volumeManager;

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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $defaultLanguage;

    /**
     * @param DocumentFactory           $documentFactory
     * @param ExtractorInterface        $extractor
     * @param VolumeManager             $volumeManager
     * @param MediaTypeManagerInterface $mediaTypeManager
     * @param FileMetaSetResolver       $metasetResolver
     * @param FileMetaDataManager       $metaDataManager
     * @param EventDispatcherInterface  $dispatcher
     * @param string                    $defaultLanguage
     */
    public function __construct(
        DocumentFactory $documentFactory,
        ExtractorInterface $extractor,
        VolumeManager $volumeManager,
        MediaTypeManagerInterface $mediaTypeManager,
        FileMetaSetResolver $metasetResolver,
        FileMetaDataManager $metaDataManager,
        EventDispatcherInterface $dispatcher,
        $defaultLanguage
    ) {
        $this->documentFactory = $documentFactory;
        $this->extractor = $extractor;
        $this->volumeManager = $volumeManager;
        $this->mediaTypeManager = $mediaTypeManager;
        $this->metasetResolver = $metasetResolver;
        $this->metaDataManager = $metaDataManager;
        $this->dispatcher = $dispatcher;
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentFactory()
    {
        return $this->documentFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentClass()
    {
        return MediaDocument::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findIdentifiers()
    {
        $indexIdentifiers = array();

        foreach ($this->volumeManager->all() as $volume) {
            /* @var $volume VolumeInterface */

            $rii = new \RecursiveIteratorIterator($volume->getIterator(), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($rii as $folder) {
                /* @var $folder FolderInterface */

                $files = $volume->findFilesByFolder($folder);

                foreach ($files as $file) {
                    /* @var $file FileInterface */

                    $fileId = $file->getId();
                    $fileVersion = $file->getVersion();

                    $identifier = sprintf('%s_%s_%s', 'media', $fileId, $fileVersion);

                    $indexIdentifiers[] = $identifier;
                }
            }
        }

        return $indexIdentifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function map($identifier)
    {
        // extract identifier parts from id
        list($prefix, $fileId, $fileVersion) = explode('_', $identifier);

        // get file object
        $volume = $this->volumeManager->getByFileId($fileId);
        $file = $volume->findFile($fileId, $fileVersion);
        $folder = $volume->findFolder($file->getFolderId());

        if (!file_exists($file->getPhysicalPath())) {
            return null;
        }

        $document = $this->mapFileToDocument($file, $folder, $volume, $identifier);

        return $document;
    }

    /**
     * Create document and fill it with values.
     *
     * @param FileInterface   $file
     * @param FolderInterface $folder
     * @param VolumeInterface $volume
     * @param int             $id
     *
     * @return DocumentInterface
     */
    private function mapFileToDocument(FileInterface $file, FolderInterface $folder, VolumeInterface $volume, $id)
    {
        // TODO do we need boosting?

        // extract content
        //$content = $this->extractContent($file);

        // Field: readablefilesize
        $formatter = new FilesizeFormatter();
        $readableFileSize = $formatter->formatFilesize($file->getSize());

        // Field: url
        $url = '/download/'.$file->getId().'/'.$file->getName();

        // Field: Parent Folder IDs

        $parentFolderIds = array();
        $parentFolder = $folder;

        while ($parentFolder) {
            $parentFolderIds[] = $parentFolder->getId();
            if (!$parentFolder->getParentId()) {
                break;
            }
            $parentFolder = $volume->findFolder($parentFolder->getParentId());
        }

        $document = $this->documentFactory->factory($this->getDocumentClass());

        $content = base64_encode(file_get_contents($file->getPhysicalPath()));

        $document
            ->setIdentity($id)
            ->set('title', $file->getName())
            ->set('folder_id', $file->getFolderID())
            ->set('parent_folder_ids', $parentFolderIds)
            ->set('file_id', $file->getID())
            ->set('file_version', $file->getVersion())
            ->set('filename', $file->getName())
            ->set('url', $url)
            ->set('mime_type', $file->getMimeType())
            ->set('media_category', $file->getMediaCategory())
            ->set('media_type', $file->getMediaType())
            ->set('filesize', $file->getSize())
            ->set('readable_filesize', $readableFileSize)
            ->set('mediafile', array(
                '_content_type' => $file->getMimeType(),
                '_name' => $file->getName(),
                '_content' => $content,
            ));

        $metasets = $this->metasetResolver->resolve($file);
        $metasetNames = array();
        $metaData = array();
        foreach ($metasets as $metaset) {
            $metasetNames[] = $metaset->getName();
            $metadata = $this->metaDataManager->findByMetaSetAndFile($metaset, $file);

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

        $event = new MapDocumentEvent($document, $file);
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
