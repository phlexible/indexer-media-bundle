<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerMediaComponent\Container;

use Phlexible\Container\ContainerBuilder;
use Phlexible\Container\Extension\Extension;
use Phlexible\Container\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Media indexer extension
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class IndexerMediaExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $configs)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../_config'));
        $loader->load('services.yml');

        $container->setAlias('indexerMediaStorage', 'indexerStorageElastica');
    }
}
