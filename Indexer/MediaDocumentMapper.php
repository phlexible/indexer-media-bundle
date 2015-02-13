<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerMediaBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerMediaBundle\IndexerMediaEvents;
use Phlexible\Bundle\MediaExtractorBundle\Extractor\ExtractorInterface;
use Phlexible\Component\Formatter\FilesizeFormatter;
use Phlexible\Component\MediaType\Model\MediaTypeManagerInterface;
use Phlexible\Component\Volume\Model\FileInterface;
use Phlexible\Component\Volume\Model\FolderInterface;
use Phlexible\Component\Volume\VolumeInterface;
use Phlexible\Component\Volume\VolumeManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Media indexer
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
     * @param EventDispatcherInterface  $dispatcher
     * @param string                    $defaultLanguage
     */
    public function __construct(
        DocumentFactory $documentFactory,
        ExtractorInterface $extractor,
        VolumeManager $volumeManager,
        MediaTypeManagerInterface $mediaTypeManager,
        EventDispatcherInterface $dispatcher,
        $defaultLanguage)
    {
        $this->documentFactory = $documentFactory;
        $this->extractor = $extractor;
        $this->volumeManager = $volumeManager;
        $this->mediaTypeManager = $mediaTypeManager;
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
        return 'Phlexible\Bundle\IndexerMediaBundle\Document\MediaDocument';
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

        $document = $this->mapFileToDocument($file, $folder, $volume, $identifier);

        return $document;
    }

    /**
     * Create document and fill it with values.
     *
     * @param FileInterface   $file
     * @param FolderInterface $folder
     * @param VolumeInterface $volume
     * @param integer         $id
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
        $url = '/download/' . $file->getId() . '/' . $file->getName();

        // Field: Parent Folder IDs

        $parentFolderIds = array();
        $parentFolder  = $folder;

        while ($parentFolder) {
            $parentFolderIds[] = $parentFolder->getId();
            if (!$parentFolder->getParentId()) {
                break;
            }
            $parentFolder = $volume->findFolder($parentFolder->getParentId());
        }

        $tags = '';

        $document = $this->documentFactory->factory($this->getDocumentClass());

        $document
            ->setIdentifier($id)
            ->setValue('title', $file->getName())
            ->setValue('tags', $tags)
            ->setValue('folder_id', $file->getFolderID())
            ->setValue('parent_folder_ids', $parentFolderIds)
            ->setValue('file_id', $file->getID())
            ->setValue('file_version', $file->getVersion())
            ->setValue('filename', $file->getName())
            ->setValue('url', $url)
            ->setValue('mime_type', $file->getMimeType())
            ->setValue('asset_type', $file->getAssetType())
            ->setValue('document_type', $file->getDocumenttype())
            ->setValue('filesize', $file->getSize())
            ->setValue('readable_filesize', $readableFileSize)
            //->setValue('content', $content)
            ->setValue('content', array(
                '_content_type' => $file->getMimeType(),
                '_name' => $file->getName(),
                'content' => base64_encode(file_get_contents($file->getPhysicalPath())))
            );

        // process meta data
        // TODO: enable
        /*
        $metaLanguage = $this->getMetaLanguage($file);
        $meta         = $asset->getMeta($metaLanguage);

        foreach ($meta as $metaKey => $metaField)
        {
            $metaFieldType  = $metaField['type'];

            if ('suggest' === $metaFieldType)
            {
                $metaFieldValue = (array) $metaField['value'];
            }
            else
            {
                $metaFieldValue = $metaField['value'];
            }

            $document->setValue('meta_' . $metaKey, $metaFieldValue, true);


            // overwrite title with title from meta information if available
            if ($document->hasValue('meta_title'))
            {
                $metaTitle = $document->getValue('meta_title');

                if (mb_strlen($metaTitle))
                {
                    $document->setValue('title', $metaTitle);
                }
            }
        }
        */

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
