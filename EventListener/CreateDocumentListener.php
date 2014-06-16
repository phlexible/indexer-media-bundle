<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaBundle\EventListener;

/**
 * Create document listener
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class CreateDocumentListener
{
    public function onCreateDocument(MWF_Core_Indexer_Event_CreateDocument $event)
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
}
