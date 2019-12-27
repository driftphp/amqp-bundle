<?php

/*
 * This file is part of the Drift Http Kernel
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Drift\AMQP\DependencyInjection;

use Mmoreram\BaseBundle\DependencyInjection\BaseConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class AMQPConfiguration.
 */
class AMQPConfiguration extends BaseConfiguration
{
    /**
     * Configure the root node.
     *
     * @param ArrayNodeDefinition $rootNode Root node
     */
    protected function setupTree(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('clients')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')
                                ->isRequired()
                            ->end()
                            ->integerNode('port')
                                ->defaultValue(5672)
                            ->end()
                            ->scalarNode('vhost')
                                ->defaultValue('/')
                            ->end()

                            ->scalarNode('user')
                                ->defaultValue('guest')
                            ->end()

                            ->scalarNode('password')
                                ->defaultValue('guest')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
