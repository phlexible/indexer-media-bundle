<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent\Document;

use Phlexible\IndexerComponent\Document\Document;

/**
 * MediaDocument
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaDocument extends Document
{
    public function __construct($documentType)
    {
        parent::__construct($documentType);

        $this->setFields(
            array(
                'title'             => array(),
                'highlight_title'   => array(self::CONFIG_READONLY),
                'tags'              => array(self::CONFIG_READONLY, self::CONFIG_MULTIVALUE),
                'copy'              => array(self::CONFIG_READONLY, self::CONFIG_MULTIVALUE),

                'folder_id'         => array(),
            	'parent_folder_ids' => array(self::CONFIG_MULTIVALUE),
                'file_id'           => array(),
                'file_version'      => array(),
                'filename'          => array(),
                'url'               => array(),
                'rawcontent'        => array(),
                'mime_type'         => array(),
                'asset_type'        => array(),
                'document_type'     => array(),
                'filesize'          => array(),
                'readable_filesize' => array(self::CONFIG_NOTINDEXED),
                'content'           => array(self::CONFIG_COPY),
            )
        );
    }
}
