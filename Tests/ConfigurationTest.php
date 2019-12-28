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

namespace Drift\AMQP\Tests;

use Bunny\AbstractClient;
use Bunny\Async\Client;
use Drift\AMQP\AMQPBundle;
use Mmoreram\BaseBundle\Kernel\DriftBaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ConfigurationTest.
 */
class ConfigurationTest extends BaseFunctionalTest
{
    /**
     * Get kernel.
     *
     * @return KernelInterface
     */
    protected static function getKernel(): KernelInterface
    {
        return new DriftBaseKernel([
            FrameworkBundle::class,
            AMQPBundle::class,
        ], [
            'parameters' => [
                'kernel.secret' => 'sdhjshjkds',
            ],
            'framework' => [
                'test' => true,
            ],
            'imports' => [
                ['resource' => dirname(__FILE__).'/clients.yml'],
            ],
            'services' => [
                'reactphp.event_loop' => [
                    'class' => LoopInterface::class,
                    'factory' => [
                        Factory::class,
                        'create',
                    ],
                ],
            ],
            'amqp' => [
                'clients' => [
                    'users' => [
                        'host' => '127.0.0.1',
                        'vhost' => '/',
                        'user' => 'guest',
                        'password' => 'guest',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test.
     */
    public function testProperConnection()
    {
        $client = static::get('amqp.users_client.test');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(AbstractClient::class, $client);
    }
}
