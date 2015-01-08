<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerMediaBundle\EventListener;

use Phlexible\Bundle\QueueBundle\Entity\Job;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Phlexible\Component\Volume\Event\CreateFileEvent;
use Phlexible\Component\Volume\Event\FileEvent;
use Phlexible\Component\Volume\Event\MoveFileEvent;
use Phlexible\Component\Volume\Event\ReplaceFileEvent;
use Phlexible\Component\Volume\Model\FileInterface;
use Phlexible\Component\Volume\VolumeEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * File listener
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class FileListener implements EventSubscriberInterface
{
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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        // TODO: activate
        return array(
            VolumeEvents::CREATE_FILE  => 'onCreateFile',
            VolumeEvents::REPLACE_FILE => 'onReplaceFile',
            VolumeEvents::MOVE_FILE    => 'onMoveFile',
            VolumeEvents::DELETE_FILE  => 'onDeleteFile',
            //VolumeEvents::DELETE_FILE => 'onSaveMeta',
        );
    }

    /**
     * @param CreateFileEvent $event
     */
    public function onCreateFile(CreateFileEvent $event)
    {
        $file = $event->getFile();

        $this->queueJob($file);
    }

    /**
     * @param ReplaceFileEvent $event
     */
    public function onReplaceFile(ReplaceFileEvent $event)
    {
        $file = $event->getFile();

        $this->queueJob($file);
    }

    /**
     * @param MoveFileEvent $event
     */
    public function onMoveFile(MoveFileEvent $event)
    {
        $file = $event->getFile();

        $this->queueJob($file);
    }

    /**
     * @param FileEvent $event
     */
    public function onDeleteFile(FileEvent $event)
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
        $file = $event->getFile();

        $this->queueJob($file);
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
