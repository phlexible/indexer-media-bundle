<?php

/*
 * This file is part of the phlexible indexer media package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerMediaBundle\Tests\DependencyInjection\Compiler;

use Phlexible\Bundle\IndexerMediaBundle\DependencyInjection\Compiler\AddIndexibleVoterPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add indexible voter pass test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class AddIndexibleVoterPassTest extends TestCase
{
    public function testProcess()
    {
        $pass = new AddIndexibleVoterPass();
        $container = new ContainerBuilder();

        $pass->process($container);
    }
}
