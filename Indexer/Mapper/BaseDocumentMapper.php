<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Indexer\Mapper;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerMediaBundle\Indexer\MediaDocumentDescriptor;
use Phlexible\Component\Formatter\FilesizeFormatter;
use Phlexible\Component\MediaManager\Volume\ExtendedFileInterface;

/**
 * Base document applier.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class BaseDocumentMapper implements MediaDocumentMapperInterface
{
    /**
     * @var FilesizeFormatter
     */
    private $formatter;

    public function __construct()
    {
        $this->formatter = new FilesizeFormatter();
    }

    /**
     * @param DocumentInterface       $document
     * @param MediaDocumentDescriptor $descriptor
     */
    public function mapDocument(DocumentInterface $document, MediaDocumentDescriptor $descriptor)
    {
        $readableFileSize = $this->formatter->formatFilesize($descriptor->getFile()->getSize());

        $parentFolderIds = array();
        $parentFolder = $descriptor->getFolder();

        while ($parentFolder) {
            $parentFolderIds[] = $parentFolder->getId();
            if (!$parentFolder->getParentId()) {
                break;
            }
            $parentFolder = $descriptor->getVolume()->findFolder($parentFolder->getParentId());
        }

        $document
            ->setIdentity($descriptor->getIdentity())
            ->set('title', $descriptor->getFile()->getName())
            ->set('folder_id', $descriptor->getFile()->getFolderId())
            ->set('parent_folder_ids', $parentFolderIds)
            ->set('file_id', $descriptor->getFile()->getId())
            ->set('file_version', $descriptor->getFile()->getVersion())
            ->set('filename', $descriptor->getFile()->getName())
            ->set('mime_type', $descriptor->getFile()->getMimeType())
            ->set('filesize', $descriptor->getFile()->getSize())
            ->set('readable_filesize', $readableFileSize);

        if ($descriptor->getFile() instanceof ExtendedFileInterface) {
            $document
                ->set('media_category', $descriptor->getFile()->getMediaCategory())
                ->set('media_type', $descriptor->getFile()->getMediaType());

        }
    }
}
