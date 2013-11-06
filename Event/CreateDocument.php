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
 * Media IndexerMedia Create Document Event
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @author      Stephan Wentz <sw@brainbits.net>
 * @copyright   2007 brainbits GmbH (http://www.brainbits.net)
 */
class Media_IndexerMedia_Event_CreateDocument extends Brainbits_Event_Notification_Abstract
{
    /**
     * @var string
     */
    protected $_notificationName = Media_IndexerMedia_Event::CREATE_DOCUMENT;

    /**
     * @var MWF_Core_Indexer_Indexer_Interface
     */
    protected $_document = null;

    /**
     * Constructor
     *
     * @param MWF_Core_Indexer_Indexer_Interface $document
     */
    public function __construct(MWF_Core_Indexer_Indexer_Interface $document)
    {
        $this->_document = $document;
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
}