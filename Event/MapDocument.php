<?php
/**
 * MWF - MAKEweb Framework
 *
 * PHP Version 5
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @copyright   2007 brainbits GmbH (http://www.brainbits.net)
 * @version     SVN: $Id: Generator.php 2312 2007-01-25 18:46:27Z swentz $
 */

/**
 * Media IndexerMedia Map Document Event
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @author      Stephan Wentz <sw@brainbits.net>
 * @copyright   2007 brainbits GmbH (http://www.brainbits.net)
 */
class Media_IndexerMedia_Event_MapDocument extends Brainbits_Event_Notification_Abstract
{
    /**
     * @var string
     */
    protected $_notificationName = Media_IndexerMedia_Event::MAP_DOCUMENT;

    /**
     * @var MWF_Core_Indexer_Indexer_Interface
     */
    protected $_document = null;

    /**
     * @var Media_Site_File_Abstract
     */
    protected $_file = null;

    /**
     * Constructor
     *
     * @param MWF_Core_Indexer_Indexer_Interface $document
     * @param Media_Site_File_Abstract           $file
     */
    public function __construct(MWF_Core_Indexer_Indexer_Interface $document, Media_Site_File_Abstract $file)
    {
        $this->_document = $document;
        $this->_file     = $file;
    }

    /**
     * Return document
     *
     * @return MWF_Core_Indexer_Indexer_Interface
     */
    public function getDocument()
    {
        return $this->_document;
    }

    /**
     * Return file
     *
     * @return Media_Site_File_Abstract
     */
    public function getFile()
    {
        return $this->_file;
    }
}