<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerMediaBundle\EventListener;

use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaIndexer;
use Phlexible\Bundle\MediaManagerBundle\Event\SaveMetaEvent;
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
     * @var MediaIndexer
     */
    private $indexer;

    /**
     * @param MediaIndexer $indexer
     */
    public function __construct(MediaIndexer $indexer)
    {
        $this->indexer = $indexer;
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

        $this->indexer->add("file_{$file->getId()}_{$file->getVersion()}", true);
    }

    /**
     * @param ReplaceFileEvent $event
     */
    public function onReplaceFile(ReplaceFileEvent $event)
    {
        $file = $event->getFile();

        $this->indexer->add("file_{$file->getId()}_{$file->getVersion()}", true);
    }

    /**
     * @param MoveFileEvent $event
     */
    public function onMoveFile(MoveFileEvent $event)
    {
        $file = $event->getFile();

        $this->indexer->add("file_{$file->getId()}_{$file->getVersion()}", true);
    }

    /**
     * @param FileEvent $event
     */
    public function onDeleteFile(FileEvent $event)
    {
        $file = $event->getFile();

        $this->indexer->delete("file_{$file->getId()}_{$file->getVersion()}", true);
    }

    public function onSaveMeta(SaveMetaEvent $event)
    {
        $file = $event->getFile();

        $this->indexer->add("file_{$file->getId()}_{$file->getVersion()}", true);
    }
}
