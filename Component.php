<?php
/**
 * Phlexible
 *
 * PHP Version 5
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */

/**
 * Media Indexer Component
 *
 * @category    Media
 * @package     Media_IndexerMedia
 * @author      Phillip Look <pl@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Media_IndexerMedia_Component extends MWF_Component_Abstract
{
    /**
     * Constructor
     * Initialses the Component values
     */
    public function __construct()
    {
        $this
            ->setVersion('0.7.0')
            ->setId('indexermedia')
            ->setFile(__FILE__)
            ->setPackage('media');
    }

    public function initContainer(MWF_Container_ContainerInterface $container)
    {
        $container->addComponents(
            array(
                'indexerMediaIndexer' => array(
                    'class' => 'Media_IndexerMedia_Indexer',
                    'arguments' => array(
                        'dispatcher',
                        'indexerDocumentFactory',
                        'mediaSiteManager',
                        ':metasets.languages.default'
                    ),
                    'scope' => 'singleton',
                    'tag'   => 'indexer.indexer',
                ),
                'indexerMediaBoost' => array(
                    'class' => 'Media_IndexerMedia_Boost',
                    'scope' => 'prototype',
                ),
                'indexerMediaQuery' => array(
                    'class'     => 'Media_IndexerMedia_Query',
                    'arguments' => array('indexerQueryParser', 'indexerMediaBoost'),
                    'scope'     => 'prototype',
                    'tag'       => 'indexer.search',
                ),
                // listeners
                'indexerMediaListenerImportFile' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => Media_Site_Event::IMPORT_FILE,
                        'callback' => array('Media_IndexerMedia_Callback', 'onImportFile'),
                    ),
                ),
                'indexerMediaListenerReplaceFile' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => Media_Site_Event::REPLACE_FILE,
                        'callback' => array('Media_IndexerMedia_Callback', 'onReplaceFile'),
                    ),
                ),
                'indexerMediaListenerMoveFile' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => Media_Site_Event::MOVE_FILE,
                        'callback' => array('Media_IndexerMedia_Callback', 'onMoveFile'),
                    ),
                ),
                'indexerMediaListenerDeleteFile' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => Media_Site_Event::DELETE_FILE,
                        'callback' => array('Media_IndexerMedia_Callback', 'onDeleteFile'),
                    ),
                ),
                'indexerMediaListenerSaveMeta' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => Media_Manager_Event::SAVE_META,
                        'callback' => array('Media_IndexerMedia_Callback', 'onSaveMeta'),
                    ),
                ),
                'indexerMediaListenerCreateDocument' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => MWF_Core_Indexer_Event::CREATE_DOCUMENT,
                        'callback' => array('Media_IndexerMedia_Callback', 'onCreateDocument'),
                    ),
                ),
            )
        );
    }
}
