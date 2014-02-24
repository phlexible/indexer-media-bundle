<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent\Listener;

/**
 * Delete file listener
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class DeleteFileListener
{
    public function onDeleteFile(Media_Site_Event_DeleteFile $event, array $params)
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
}
