<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Document;

use Phlexible\Bundle\IndexerBundle\Document\Document;

/**
 * Media document
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaDocument extends Document
{
    /**
     * @param string $documentType
     */
    public function __construct($documentType)
    {
        parent::__construct($documentType);

        $this->setFields(
            array(
                'title'             => array(),
                'highlight_title'   => array(self::CONFIG_READONLY),
                'tags'              => array(self::CONFIG_MULTIVALUE => true, self::CONFIG_READONLY => true),
                'copy'              => array(self::CONFIG_MULTIVALUE => true, self::CONFIG_READONLY => true),

                'folder_id'         => array(),
                'parent_folder_ids' => array(self::CONFIG_MULTIVALUE => true),
                'file_id'           => array(),
                'file_version'      => array(self::CONFIG_TYPE => self::TYPE_INTEGER),
                'filename'          => array(),
                'url'               => array(),
                'rawcontent'        => array(),
                'mime_type'         => array(),
                'asset_type'        => array(),
                'document_type'     => array(),
                'filesize'          => array(self::CONFIG_TYPE => self::TYPE_INTEGER),
                'readable_filesize' => array(self::CONFIG_NOTINDEXED => true),
                'content'           => array(self::CONFIG_TYPE => self::TYPE_COPY),
            )
        );
    }
}
