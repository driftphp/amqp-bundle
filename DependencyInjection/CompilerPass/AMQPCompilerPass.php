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
            static::registerClient($container, $clientName, $clientConfiguration);
        }
    }

    /**
     * Register client.
     *
     * @param ContainerBuilder $container
     * @param string           $clientName
     * @param array            $clientConfiguration
     */
    public static function registerClient(
        ContainerBuilder $container,
        string $clientName,
        array $clientConfiguration
    ) {
        static::createConnectableClient($container, $clientName, $clientConfiguration);
        static::createClient($container, $clientName);
        static::createChannel($container, $clientName, \boolval($clientConfiguration['preload']));
    }

    /**
     * Create connectable client.
     *
     * @param ContainerBuilder $container
     * @param string           $clientName
     * @param array            $clientConfiguration
     */
    public static function createConnectableClient(
        ContainerBuilder $container,
        string $clientName,
        array $clientConfiguration
    ) {
        $connectableClientId = "amqp.{$clientName}_connectable_client";
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
     * @param string           $clientName
     */
    public static function createClient(
        ContainerBuilder $container,
        string $clientName
    ) {
        $connectableClientId = "amqp.{$clientName}_connectable_client";
        $clientId = "amqp.{$clientName}_client";
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

        $container->setAlias(Client::class, $clientId);
        $container->setAlias(AbstractClient::class, $clientId);
        $container->registerAliasForArgument($clientId, Client::class, "{$clientName} client");
        $container->registerAliasForArgument($clientId, AbstractClient::class, "{$clientName} client");
    }

    /**
     * Create channel.
     *
     * @param ContainerBuilder $container
     * @param string           $clientName
     * @param bool             $preload
     */
    public static function createChannel(
        ContainerBuilder $container,
        string $clientName,
        bool $preload
    ) {
        $channelId = "amqp.{$clientName}_channel";
        $clientId = "amqp.{$clientName}_client";
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

        $container->setAlias(Channel::class, $channelId);
        $container->registerAliasForArgument($channelId, Channel::class, "{$clientName} channel");
    }
}
