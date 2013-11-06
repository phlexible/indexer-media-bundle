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
 * Media Indexer Callback
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @author      Phillip Look <pl@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Media_IndexerMedia_Callback
{
    public static function onCreateDocument(MWF_Core_Indexer_Event_CreateDocument $event)
    {
        $document = $event->getDocument();

        if ('media' !== $document->getDocumentType())
        {
            return;
        }

        $document->setFields(
            array(
                'title'             => array(),
                'highlight_title'   => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY),
                'tags'              => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY, MWF_Core_Indexer_Document_Interface::CONFIG_MULTIVALUE),
                'copy'              => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY, MWF_Core_Indexer_Document_Interface::CONFIG_MULTIVALUE),

                'folder_id'         => array(),
            	'parent_folder_ids' => array(MWF_Core_Indexer_Document_Interface::CONFIG_MULTIVALUE),
                'file_id'           => array(),
                'file_version'      => array(),
                'filename'          => array(),
                'url'               => array(),
                'rawcontent'        => array(),
                'mime_type'         => array(),
                'asset_type'        => array(),
                'document_type'     => array(),
                'filesize'          => array(),
                'readable_filesize' => array(MWF_Core_Indexer_Document_Interface::CONFIG_NOTINDEXED),
                'content'           => array(MWF_Core_Indexer_Document_Interface::CONFIG_COPY),
            )
        );
    }

    public static function onImportFile(Media_Site_Event_ImportFile $event, array $params)
    {
        $container = $params['container'];
        $file = $event->getFile();

        self::_updateFile($file, $container);
    }

    public static function onReplaceFile(Media_Site_Event_ReplaceFile $event, array $params)
    {
        $container = $params['container'];
        $file = $event->getFile();

        self::_updateFile($file, $container);
    }

    public static function onMoveFile(Media_Site_Event_MoveFile $event, array $params)
    {
        $container = $params['container'];
        $file = $event->getFile();

        self::_updateFile($file, $container);
    }

    public static function onSaveMeta(Media_Manager_Event_SaveMeta $event, array $params)
    {
        $container = $params['container'];
        $file = $event->getFile();

        self::_updateFile($file, $container);
    }

    public static function onDeleteFile(Media_Site_Event_DeleteFile $event, array $params)
    {
        $container = $params['container'];
        $file = $event->getFile();

        /* @var $indexerTools MWF_Core_Indexer_Tools */
        $indexerTools = $container->indexerTools;
        $storages = $indexerTools->getRepositoriesByAcceptedStorage('media');

        $identifier = 'file_' . $file->getId() . '_' . $file->getVersion();

        foreach ($storages as $repository)
        {
            $repository->removeByIdentifier($identifier);
        }
    }

    protected static function _updateFile(Media_Site_File_Abstract $file, MWF_Container_ContainerInterface $container)
    {
        $queueManager = $container->queueManager;

        /* @var $indexerTools MWF_Core_Indexer_Tools */
        $indexerTools = $container->indexerTools;
        $storages = $indexerTools->getRepositoriesByAcceptedStorage('media');

        $identifier = 'file_' . $file->getId() . '_' . $file->getVersion();

        $job = new MWF_Core_Indexer_Job_AddNode();
        $job->setIdentifier($identifier);
        $job->setStorageIds(array_keys($storages));
        $job->setIndexerId('media');

        $queueManager->addUniqueJob($job, MWF_Core_Queue_Manager::PRIORITY_LOW);
    }
}
