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
use Phlexible\Bundle\MediaSiteBundle\File\FileInterface;
use Phlexible\Bundle\MediaSiteBundle\MediaSiteEvents;
use Phlexible\Bundle\QueueBundle\Entity\Job;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
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
        // TODO: activate
        return array();
        return array(
            MediaSiteEvents::CREATE_FILE => 'onCreateFile',
            MediaSiteEvents::REPLACE_FILE => 'onReplaceFile',
            MediaSiteEvents::MOVE_FILE => 'onMoveFile',
            MediaSiteEvents::DELETE_FILE => 'onDeleteFile',
            //MediaSiteEvents::DELETE_FILE => 'onSaveMeta',
        );
    }

    /**
     * @param CreateFileEvent $event
     */
    public function onCreateFile(CreateFileEvent $event)
    {
        $file = $event->getAction()->getFile();

        $this->queueJob($file);
    }

    /**
     * @param ReplaceFileEvent $event
     */
    public function onReplaceFile(ReplaceFileEvent $event)
    {
        $file = $event->getAction()->getFile();

        $this->queueJob($file);
    }

    /**
     * @param MoveFileEvent $event
     */
    public function onMoveFile(MoveFileEvent $event)
    {
        $file = $event->getAction()->getFile();

        $this->queueJob($file);
    }

    /**
     * @param DeleteFileEvent $event
     */
    public function onDeleteFile(DeleteFileEvent $event)
    {
        $file = $event->getAction()->getFile();

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
        $file = $event->getFile();

        $this->queueJob($file);
    }

    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    /**
     * @param JobManagerInterface $jobManager
     */
    public function __construct(JobManagerInterface $jobManager)
    {
        $this->jobManager = $jobManager;
    }

    /**
     * @param FileInterface $file
     */
    private function queueJob(FileInterface $file)
    {
        $identifier = 'file_' . $file->getId() . '_' . $file->getVersion();

        $job = new Job('indexer-media', array('--documentId', $identifier));
        $this->jobManager->addUniqueJob($job);
    }
}
