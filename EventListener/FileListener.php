<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\EventListener;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaIndexer;
use Phlexible\Component\Volume\Event\FileEvent;
use Phlexible\Component\Volume\VolumeEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * File listener.
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
            VolumeEvents::CREATE_FILE => 'onCreateFile',
            VolumeEvents::REPLACE_FILE => 'onReplaceFile',
            VolumeEvents::MOVE_FILE => 'onMoveFile',
            VolumeEvents::DELETE_FILE => 'onDeleteFile',
        );
    }

    /**
     * @param FileEvent $event
     */
    public function onCreateFile(FileEvent $event)
    {
        $file = $event->getFile();

        $this->indexer->add(new DocumentIdentity("media_{$file->getId()}_{$file->getVersion()}"), true);
    }

    /**
     * @param FileEvent $event
     */
    public function onReplaceFile(FileEvent $event)
    {
        $file = $event->getFile();

        $this->indexer->add(new DocumentIdentity("media_{$file->getId()}_{$file->getVersion()}"), true);
    }

    /**
     * @param FileEvent $event
     */
    public function onMoveFile(FileEvent $event)
    {
        $file = $event->getFile();

        $this->indexer->add(new DocumentIdentity("media_{$file->getId()}_{$file->getVersion()}"), true);
    }

    /**
     * @param FileEvent $event
     */
    public function onDeleteFile(FileEvent $event)
    {
        $file = $event->getFile();

        $this->indexer->add(new DocumentIdentity("media_{$file->getId()}_{$file->getVersion()}"), true);
    }
}
