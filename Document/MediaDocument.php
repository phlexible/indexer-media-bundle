<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Document;

use Phlexible\Bundle\IndexerBundle\Document\Document;

/**
 * Media document.
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class MediaDocument extends Document
{
    public function __construct()
    {
        $this->setFields(
            array(
                'title' => array('type' => self::TYPE_STRING),
                'highlight_title' => array('type' => self::TYPE_STRING, 'readonly' => true),
                'tags' => array('type' => self::TYPE_TEXT, 'array' => true),
                //'copy'              => array('type' => self::TYPE_TEXT, 'array' => true,'readonly' => true),
                'content' => array('type' => self::TYPE_STRING),
                'mediafile' => array('type' => 'attachment', 'fields' => array(
                    'content' => array('store' => false, 'index' => true),
                    'name' => array('store' => true, 'index' => true),
                    'content_type' => array('store' => true, 'index' => true),
                )),

                'folder_id' => array('type' => self::TYPE_STRING),
                'parent_folder_ids' => array('type' => self::TYPE_STRING, 'array' => true),
                'file_id' => array('type' => self::TYPE_STRING),
                'file_version' => array('type' => self::TYPE_INTEGER),
                'filename' => array('type' => self::TYPE_STRING),
                'url' => array('type' => self::TYPE_STRING),
                'mime_type' => array('type' => self::TYPE_STRING),
                'media_category' => array('type' => self::TYPE_STRING),
                'media_type' => array('type' => self::TYPE_STRING),
                'filesize' => array('type' => self::TYPE_INTEGER),
                'readable_filesize' => array('type' => self::TYPE_STRING, 'indexed' => false),
                'metasets' => array('type' => self::TYPE_STRING, 'array' => true),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'media';
    }
}
