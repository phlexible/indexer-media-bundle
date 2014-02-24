<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent\Listener;

/**
 * File listener
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class FileListener
{
    public function onImportFile(Media_Site_Event_ImportFile $event, array $params)
    {
        $container = $params['container'];
        $file = $event->getFile();

        $this->_updateFile($file, $container);
    }

    public function onReplaceFile(Media_Site_Event_ReplaceFile $event, array $params)
    {
        $container = $params['container'];
        $file = $event->getFile();

        $this->_updateFile($file, $container);
    }

    public function onMoveFile(Media_Site_Event_MoveFile $event, array $params)
    {
        $container = $params['container'];
        $file = $event->getFile();

        $this->_updateFile($file, $container);
    }

    public function onSaveMeta(Media_Manager_Event_SaveMeta $event, array $params)
    {
        $container = $params['container'];
        $file = $event->getFile();

        $this->_updateFile($file, $container);
    }

    private function _updateFile(Media_Site_File_Abstract $file, MWF_Container_ContainerInterface $container)
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
