<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerMediaBundle\EventListener;

use Phlexible\Bundle\MediaSiteBundle\Event\CreateFileEvent;
use Phlexible\Bundle\MediaSiteBundle\Event\DeleteFileEvent;
use Phlexible\Bundle\MediaSiteBundle\Event\MoveFileEvent;
use Phlexible\Bundle\MediaSiteBundle\Event\ReplaceFileEvent;
use Phlexible\Bundle\MediaSiteBundle\MediaSiteEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * File listener
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class FileListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array();
        return array(
            MediaSiteEvents::CREATE_FILE => 'onCreateFile',
            MediaSiteEvents::REPLACE_FILE => 'onReplaceFile',
            MediaSiteEvents::MOVE_FILE => 'onMoveFile',
            MediaSiteEvents::DELETE_FILE => 'onDeleteFile',
            //MediaSiteEvents::DELETE_FILE => 'onSaveMeta',
        );
    }

    public function onCreateFile(CreateFileEvent $event)
    {
        $file = $event->getFile();

        $this->_updateFile($file, $container);
    }

    public function onReplaceFile(ReplaceFileEvent $event)
    {
        $file = $event->getFile();

        $this->_updateFile($file, $container);
    }

    public function onMoveFile(MoveFileEvent $event)
    {
        $file = $event->getFile();

        $this->_updateFile($file, $container);
    }

    public function onDeleteFile(DeleteFileEvent $event)
    {
        $file = $event->getFile();

        /* @var $indexerTools MWF_Core_Indexer_Tools */
        $indexerTools = $container->get('indexer.tools');
        $storages = $indexerTools->getRepositoriesByAcceptedStorage('media');

        $identifier = 'file_' . $file->getId() . '_' . $file->getVersion();

        foreach ($storages as $repository)
        {
            $repository->removeByIdentifier($identifier);
        }
    }

    public function onSaveMeta(Media_Manager_Event_SaveMeta $event)
    {
        $container = $params['container'];
        $file = $event->getFile();

        $this->_updateFile($file, $container);
    }

    private function _updateFile(Media_Site_File_Abstract $file, MWF_Container_ContainerInterface $container)
    {
        $queueManager = $container->queueManager;

        /* @var $indexerTools MWF_Core_Indexer_Tools */
        $indexerTools = $container->get('indexer.tools');
        $storages = $indexerTools->getRepositoriesByAcceptedStorage('media');

        $identifier = 'file_' . $file->getId() . '_' . $file->getVersion();

        $job = new MWF_Core_Indexer_Job_AddNode();
        $job->setIdentifier($identifier);
        $job->setStorageIds(array_keys($storages));
        $job->setIndexerId('media');

        $queueManager->addUniqueJob($job, MWF_Core_Queue_Manager::PRIORITY_LOW);
    }
}
