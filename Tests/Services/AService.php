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

namespace Drift\AMQP\Tests\Services;

use Bunny\Async\Client;
use Bunny\Channel;

/**
 * Class AService.
 */
class AService
{
    /**
     * @var Client
     */
    private $client1;

    /**
     * @var Client
     */
    private $client2;

    /**
     * @var Client
     */
    private $client3;

    /**
     * @var Channel
     */
    private $channel1;

    /**
     * @var Channel
     */
    private $channel2;

    /**
     * @var Channel
     */
    private $channel3;

    /**
     * AService constructor.
     *
     * @param Client  $usersClient
     * @param Client  $ordersClient
     * @param Client  $users2Client
     * @param Channel $usersChannel
     * @param Channel $ordersChannel
     * @param Channel $users2Channel
     */
    public function __construct(
        Client $usersClient,
        Client $ordersClient,
        Client $users2Client,
        Channel $usersChannel,
        Channel $ordersChannel,
        Channel $users2Channel
    ) {
        $this->client1 = $usersClient;
        $this->client2 = $ordersClient;
        $this->client3 = $users2Client;
        $this->channel1 = $usersChannel;
        $this->channel2 = $ordersChannel;
        $this->channel3 = $users2Channel;
    }

    /**
     * Are equal.
     */
    public function areOK()
    {
        return $this->client1 !== $this->client2
            && $this->client1 === $this->client3
            && $this->channel1 !== $this->channel2
            && $this->channel1 === $this->channel3;
    }
}
