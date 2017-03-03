<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add indexible voter pass.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class AddIndexibleVoterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $indexibleVoters = array();
        foreach ($container->findTaggedServiceIds('phlexible_indexer_media.indexible_voter') as $id => $attributes) {
            $indexibleVoters[] = new Reference($id);
        }

        if (!empty($indexibleVoters)) {
            $container->getDefinition('phlexible_indexer_media.indexible_voter')->replaceArgument(0, $indexibleVoters);
        }

        $indexibleContentVoters = array();
        foreach ($container->findTaggedServiceIds('phlexible_indexer_media.indexible_content_voter') as $id => $attributes) {
            $indexibleContentVoters[] = new Reference($id);
        }

        if (!empty($indexibleContentVoters)) {
            $container->getDefinition('phlexible_indexer_media.indexible_content_voter')->replaceArgument(0, $indexibleContentVoters);
        }
    }
}
