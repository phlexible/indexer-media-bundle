<?php
/**
 * Phlexible
 *
 * PHP Version 5
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */

/**
 * Media Indexer
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @author      Phillip Look <pl@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Media_IndexerMedia_Indexer extends MWF_Core_Indexer_Indexer_Abstract
{
    /**
     * @var string
     */
    const DOCUMENT_TYPE = 'media';
    
    /**
     * @var Brainbits_Event_Dispatcher
     */
    protected $dispatcher;

    /**
     * @var MWF_Core_Indexer_Document_Factory
     */
    protected $documentFactory;

    /**
     * @var Media_Site_Manager
     */
    protected $mediaSiteManager;

    /**
     * @var string
     */
    protected $defaultLanguage;

    /**
     * @var string
     */
    protected $_label = 'Indexer for Media';

    /**
     * Constructor
     *
     * @param Brainbits_Event_Dispatcher        $dispatcher
     * @param MWF_Core_Indexer_Document_Factory $documentFactory
     * @param Media_Site_Manager                $mediaSiteManager
     * @param string                            $defaultLanguage
     */
    public function __construct(Brainbits_Event_Dispatcher $dispatcher,
                                MWF_Core_Indexer_Document_Factory $documentFactory,
                                Media_Site_Manager $mediaSiteManager,
                                $defaultLanguage)
    {
        $this->dispatcher       = $dispatcher;
        $this->documentFactory  = $documentFactory;
        $this->mediaSiteManager = $mediaSiteManager;
        $this->defaultLanguage  = $defaultLanguage;
    }

    /**
     * Return document class
     *
     * @return string
     */
    public function getDocumentClass()
    {
        return 'MWF_Core_Indexer_Document';
    }

    /**
     * Return document type
     *
     * @return string
     */
    public function getDocumentType()
    {
        return self::DOCUMENT_TYPE;
    }

    /**
     * Return all identifiers
     *
     * @return array
     */
    public function getAllIdentifiers()
    {
        $indexIdentifiers = array();

        $mediaSites = $this->mediaSiteManager->getAll();

        foreach ($mediaSites as $mediaSite)
        {
            /* @var $mediaSite Media_Site_Abstract */

            $rii = new RecursiveIteratorIterator($mediaSite->getIterator(), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($rii as $folder)
            {
                /* @var $folder Media_Site_Folder_Abstract */

                $files = $folder->getFiles();

                foreach ($files as $file)
                {
                    /* @var $file Media_Site_File_Abstract */

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
     * Get document by identifier
     *
     * @return MWF_Core_Indexer_Document
     */
    public function getDocumentByIdentifier($id)
    {
        // extract identifier parts from id
        list($prefix, $fileId, $fileVersion) = explode('_', $id);

        // get file object
        $mediaSite = $this->mediaSiteManager->getByFileId($fileId);
        $filePeer  = $mediaSite->getFilePeer();
        $file      = $filePeer->getByID($fileId, $fileVersion);

        $document = $this->_mapFileToDocument($file, $id);

        return $document;
    }

    /**
     * Create document and fill it with values.
     *
     * @param Media_Site_File_Abstract $file
     * @param integer                  $id
     *
     * @return MWF_Core_Indexer_Document
     */
    protected function _mapFileToDocument(Media_Site_File_Abstract $file, $id)
    {
        // TODO do we need boosting?

        // extract content
        $asset   = $file->getAsset();
        $content = $this->extractContent($asset);

        // Field: mediatype
        $assetType = preg_replace('/[^\w]/u', '', strtolower($file->getAssetType()));

        // Field: readablefilesize
        $readableFileSize = Brainbits_Format_Filesize::format($file->getSize());

        // Field: url
        $url = '/download/' . $file->getId() . '/' . $file->getName();

        // Field: Parent Folder IDs
        
        $parentFolderIds = array();
        $parentFolder 	 = $file->getFolder();
               
        while ($parentFolder !== null)
        {
        	$parentFolderIds[] = $parentFolder->getID();
        	$parentFolder = $parentFolder->getParent();
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
            ->setValue('document_type', $file->getDocumentTypeKey())
            ->setValue('filesize', $file->getSize())
            ->setValue('readable_filesize', $readableFileSize)
            ->setValue('content', $content);

        // process meta data
        $metaLanguage = $this->_getMetaLanguage($asset);
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

        $event = new Media_IndexerMedia_Event_MapDocument($document, $file);
        $this->dispatcher->postNotification($event);

        return $document;
    }

    /**
     * Extract content from asset type.
     *
     * @param Media_Asset_Interface $asset
     */
    public function extractContent(Media_Asset_Interface $asset)
    {
        // parse content
        $content = $asset->getContent();

        // Remove NL, CR, TABs
        $content = str_replace(array("\r", "\n", "\t"), ' ', $content);

        // Remove multiple whitespaces
        $content = preg_replace('/\s+/u', ' ', $content);

        // trim content
        $content = trim($content);

        return $content;
    }

    protected function _getMetaLanguage(Media_Asset_Abstract $asset)
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
