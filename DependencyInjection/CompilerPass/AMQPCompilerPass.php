<?php

/*
 * This file is part of the Drift Project
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
use Bunny\Channel;
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
     * Register client.
     *
     * @param ContainerBuilder $container
     * @param string           $clientName
     * @param array            $clientConfiguration
     */
    private function registerClient(
        ContainerBuilder $container,
        string $clientName,
        array $clientConfiguration
    ) {
        $clientHash = substr(md5(json_encode($clientConfiguration)), 0, 7);
        $clientHashId = "amqp.client.{$clientHash}";
        $channelHashId = "amqp.channel.{$clientHash}";
        $clientId = "amqp.{$clientName}_client";
        $channelId = "amqp.{$clientName}_channel";

        if (!$container->has($clientHashId)) {
            $this->createConnectableClient($container, $clientHash, $clientConfiguration);
            $this->createClient($container, $clientHash);
            $this->createChannel($container, $clientHash, \boolval($clientConfiguration['preload']));
        }

        $container->setAlias(
            $clientId,
            $clientHashId
        );

        $container->setAlias(
            Client::class,
            $clientHashId
        );

        $container->setAlias(
            AbstractClient::class,
            $clientHashId
        );

        $container->setAlias(
            $channelId,
            $channelHashId
        );

        $container->setAlias(
            Channel::class,
            $channelHashId
        );

        $container->registerAliasForArgument($clientHashId, Client::class, "{$clientName} client");
        $container->registerAliasForArgument($clientHashId, AbstractClient::class, "{$clientName} client");
        $container->registerAliasForArgument($channelHashId, Channel::class, "{$clientName} channel");
    }

    /**
     * Create connectable client.
     *
     * @param ContainerBuilder $container
     * @param string           $clientHash
     * @param array            $clientConfiguration
     */
    private function createConnectableClient(
        ContainerBuilder $container,
        string $clientHash,
        array $clientConfiguration
    ) {
        $connectableClientId = "amqp.connectable_client.{$clientHash}";
        $clientDefinition = new Definition(
            Client::class,
            [
                new Reference('reactphp.event_loop'),
                $clientConfiguration,
            ]
        );

        $container->setDefinition(
            $connectableClientId,
            $clientDefinition
        );
    }

    /**
     * Create connectable client.
     *
     * @param ContainerBuilder $container
     * @param string           $clientHash
     */
    private function createClient(
        ContainerBuilder $container,
        string $clientHash
    ) {
        $connectableClientId = "amqp.connectable_client.{$clientHash}";
        $clientId = "amqp.client.{$clientHash}";
        $clientDefinition = new Definition(Client::class);
        $clientDefinition->setPrivate(true);
        $clientDefinition->addTag('await');
        $clientDefinition->setFactory([
            new Reference($connectableClientId),
            'connect',
        ]);

        $container->setDefinition(
            $clientId,
            $clientDefinition
        );
    }

    /**
     * Create channel.
     *
     * @param ContainerBuilder $container
     * @param string           $clientHash
     * @param bool             $preload
     */
    private function createChannel(
        ContainerBuilder $container,
        string $clientHash,
        bool $preload
    ) {
        $channelId = "amqp.channel.{$clientHash}";
        $clientId = "amqp.client.{$clientHash}";
        $channelDefinition = new Definition(Channel::class);

        $channelDefinition->addTag('await');

        if ($preload) {
            $channelDefinition->addTag('preload');
        }

        $channelDefinition->setFactory([
            new Reference($clientId),
            'channel',
        ]);

        $container->setDefinition(
            $channelId,
            $channelDefinition
        );
    }
}
