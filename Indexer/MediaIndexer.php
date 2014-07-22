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
use Phlexible\Bundle\IndexerBundle\Indexer\AbstractIndexer;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerMediaBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerMediaBundle\IndexerMediaEvents;
use Phlexible\Bundle\MediaExtractorBundle\ContentExtractor\ContentExtractorInterface;
use Phlexible\Bundle\MediaSiteBundle\Model\FileInterface;
use Phlexible\Bundle\MediaSiteBundle\Model\FolderInterface;
use Phlexible\Bundle\MediaSiteBundle\Site\SiteInterface;
use Phlexible\Bundle\MediaSiteBundle\Site\SiteManager;
use Phlexible\Component\Formatter\FilesizeFormatter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Media indexer
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaIndexer extends AbstractIndexer
{
    /**
     * @var string
     */
    const DOCUMENT_TYPE = 'media';

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @var ContentExtractorInterface
     */
    private $contentExtractor;

    /**
     * @var SiteManager
     */
    private $siteManager;

    /**
     * @var string
     */
    private $defaultLanguage;

    /**
     * @param EventDispatcherInterface  $dispatcher
     * @param StorageInterface          $storage
     * @param DocumentFactory           $documentFactory
     * @param ContentExtractorInterface $contentExtractor
     * @param SiteManager               $siteManager
     * @param string                    $defaultLanguage
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                StorageInterface $storage,
                                DocumentFactory $documentFactory,
                                ContentExtractorInterface $contentExtractor,
                                SiteManager $siteManager,
                                $defaultLanguage)
    {
        $this->dispatcher = $dispatcher;
        $this->storage = $storage;
        $this->documentFactory = $documentFactory;
        $this->contentExtractor = $contentExtractor;
        $this->siteManager = $siteManager;
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Media indexer';
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
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
    public function getDocumentType()
    {
        return self::DOCUMENT_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllIdentifiers()
    {
        $indexIdentifiers = array();

        $sites = $this->siteManager->getAll();

        foreach ($sites as $site) {
            /* @var $site SiteInterface */

            $rii = new \RecursiveIteratorIterator($site->getIterator(), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($rii as $folder) {
                /* @var $folder FolderInterface */

                $files = $site->findFilesByFolder($folder);

                foreach ($files as $file) {
                    /* @var $file FileInterface */

                    $fileId = $file->getId();
                    $fileVersion = $file->getVersion();

                    $identifier = 'file_' . $fileId . '_' . $fileVersion;

                    $indexIdentifiers[] = $identifier;
                }
            }
        }

        return $indexIdentifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentByIdentifier($id)
    {
        // extract identifier parts from id
        list($prefix, $fileId, $fileVersion) = explode('_', $id);

        // get file object
        $site = $this->siteManager->getByFileId($fileId);
        $file = $site->findFile($fileId, $fileVersion);
        $folder = $site->findFolder($file->getFolderId());

        $document = $this->mapFileToDocument($file, $folder, $site, $id);

        return $document;
    }

    /**
     * Create document and fill it with values.
     *
     * @param FileInterface   $file
     * @param FolderInterface $folder
     * @param SiteInterface   $site
     * @param integer         $id
     *
     * @return DocumentInterface
     */
    private function mapFileToDocument(FileInterface $file, FolderInterface $folder, SiteInterface $site, $id)
    {
        // TODO do we need boosting?

        // extract content
        $content = $this->extractContent($file);

        // Field: mediatype
        $assetType = preg_replace('/[^\w]/u', '', strtolower($file->getAttribute('documenttype')));

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
            $parentFolder = $site->findFolder($parentFolder->getParentId());
        }

        $tags = '';

        $document = $this->createDocument();

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
            ->setValue('asset_type', $assetType)
            ->setValue('document_type', $file->getAttribute('documenttype'))
            ->setValue('filesize', $file->getSize())
            ->setValue('readable_filesize', $readableFileSize)
            ->setValue('content', $content);

        // process meta data
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
        $content = trim((string) $this->contentExtractor->extract($file));

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
