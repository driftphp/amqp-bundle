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

namespace Drift\AMQP\DependencyInjection\CompilerPass;

use Bunny\AbstractClient;
use Bunny\Async\Client;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AMQPCompilerPass.
 */
class AMQPCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        $clientsConfiguration = $container->getParameter('amqp.clients_configuration');
        if (empty($clientsConfiguration)) {
            return;
        }

        foreach ($clientsConfiguration as $clientName => $clientConfiguration) {
            ksort($clientConfiguration);
            $this->registerClient($container, $clientName, $clientConfiguration);
        }
    }

    /**
     * Register client
     *
     * @param ContainerBuilder $container
     * @param string $clientName
     * @param array $clientConfiguration
     */
    private function registerClient(
        ContainerBuilder $container,
        string $clientName,
        array $clientConfiguration
    )
    {
        $clientId = "amqp.{$clientName}_client";
        $clientConfigurationHash = substr(md5(json_encode($clientConfiguration)), 0, 7);
        $clientConfigurationHash = "amqp.client.{$clientConfigurationHash}";
        if (!$container->has($clientConfigurationHash)) {
            $definition = new Definition(
                Client::class,
                [
                    new Reference('reactphp.event_loop'),
                    $clientConfiguration
                ]
            );

            $definition->setMethodCalls([
                ['connect', []]
            ]);

            $container->setDefinition(
                $clientConfigurationHash,
                $definition
            );
        }

        $container->setAlias(
            $clientId,
            $clientConfigurationHash
        );

        $container->setAlias(
            Client::class,
            $clientConfigurationHash
        );

        $container->setAlias(
            AbstractClient::class,
            $clientConfigurationHash
        );

        $container->registerAliasForArgument($clientConfigurationHash, Client::class, "{$clientName} client");
        $container->registerAliasForArgument($clientConfigurationHash, AbstractClient::class, "{$clientName} client");
    }
}
