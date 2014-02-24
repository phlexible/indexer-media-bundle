<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent\Indexer;

use Phlexible\Event\EventDispatcher;
use Phlexible\IndexerComponent\Document\DocumentFactory;
use Phlexible\IndexerComponent\Document\DocumentInterface;
use Phlexible\IndexerComponent\Indexer\AbstractIndexer;
use Phlexible\IndexerComponent\Storage\StorageInterface;
use Phlexible\IndexerMediaComponent\Event\MapDocumentEvent;
use Phlexible\MediaAssetComponent\Asset;
use Phlexible\MediaAssetComponent\AssetManager;
use Phlexible\MediaAssetComponent\ContentExtractor\ContentExtractorInterface;
use Phlexible\MediaSiteComponent\File\FileInterface;
use Phlexible\MediaSiteComponent\Folder\FolderInterface;
use Phlexible\MediaSiteComponent\Site\SiteInterface;
use Phlexible\MediaSiteComponent\Site\SiteManager;

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
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var DocumentFactory
     */
    protected $documentFactory;

    /**
     * @var AssetManager
     */
    protected $assetManager;

    /**
     * @var ContentExtractorInterface
     */
    protected $contentExtractor;

    /**
     * @var SiteManager
     */
    protected $siteManager;

    /**
     * @var string
     */
    protected $defaultLanguage;

    /**
     * @param EventDispatcher           $dispatcher
     * @param StorageInterface          $storage
     * @param DocumentFactory           $documentFactory
     * @param AssetManager              $assetManager
     * @param ContentExtractorInterface $contentExtractor
     * @param SiteManager               $siteManager
     * @param string                    $defaultLanguage
     */
    public function __construct(EventDispatcher $dispatcher,
                                StorageInterface $storage,
                                DocumentFactory $documentFactory,
                                AssetManager $assetManager,
                                ContentExtractorInterface $contentExtractor,
                                SiteManager $siteManager,
                                $defaultLanguage)
    {
        $this->dispatcher = $dispatcher;
        $this->storage = $storage;
        $this->documentFactory = $documentFactory;
        $this->assetManager = $assetManager;
        $this->contentExtractor = $contentExtractor;
        $this->siteManager = $siteManager;
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return 'Media indexer';
    }

    /**
     * @inheritDoc
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentFactory()
    {
        return $this->documentFactory;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentClass()
    {
        return 'Phlexible\IndexerMediaComponent\Document\MediaDocument';
    }

    /**
     * @inheritDoc
     */
    public function getDocumentType()
    {
        return self::DOCUMENT_TYPE;
    }

    /**
     * @inheritDoc
     */
    public function getAllIdentifiers()
    {
        $indexIdentifiers = array();

        $sites = $this->siteManager->getAll();

        foreach ($sites as $site)
        {
            /* @var $site SiteInterface */

            $rii = new \RecursiveIteratorIterator($site->getIterator(), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($rii as $folder)
            {
                /* @var $folder FolderInterface */

                $files = $site->findFilesByFolder($folder);

                foreach ($files as $file)
                {
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
     * @inheritDoc
     */
    public function getDocumentByIdentifier($id)
    {
        // extract identifier parts from id
        list($prefix, $fileId, $fileVersion) = explode('_', $id);

        // get file object
        $site = $this->siteManager->getByFileId($fileId);
        $file = $site->findFile($fileId);
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
     * @return DocumentInterface
     */
    private function mapFileToDocument(FileInterface $file, FolderInterface $folder, SiteInterface $site, $id)
    {
        // TODO do we need boosting?

        // extract content
        $asset   = $this->assetManager->find($file);
        $content = $this->extractContent($asset);

        // Field: mediatype
        $assetType = preg_replace('/[^\w]/u', '', strtolower($asset->getDocumenttype()->getType()));

        // Field: readablefilesize
        $readableFileSize = \Brainbits_Format_Filesize::format($file->getSize());

        // Field: url
        $url = '/download/' . $file->getId() . '/' . $file->getName();

        // Field: Parent Folder IDs
        
        $parentFolderIds = array();
        $parentFolder  = $folder;

        while ($parentFolder)
        {
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
            ->setValue('document_type', $asset->getDocumenttype()->getKey())
            ->setValue('filesize', $file->getSize())
            ->setValue('readable_filesize', $readableFileSize)
            ->setValue('content', $content);

        // process meta data
        $metaLanguage = $this->_getMetaLanguage($asset);
        $meta         = $asset->getMeta($metaLanguage);

        /*
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
        $this->dispatcher->dispatch($event);

        return $document;
    }

    /**
     * Extract content from asset.
     *
     * @param Asset $asset
     * @return string
     */
    private function extractContent(Asset $asset)
    {
        // parse content
        $content = trim((string)$this->contentExtractor->extract($asset));

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

    private function _getMetaLanguage(Asset $asset)
    {
        // use meta default language as fallback
        $metaLanguage = $this->defaultLanguage;

        $meta = $asset->getMeta($metaLanguage);
        if (isset($meta['language']['value']) && strlen($meta['language']['value']))
        {
            // use the language of the document for indexing meta informations
            $metaLanguage = $meta['language']['value'];
        }

        return $metaLanguage;
    }

}
