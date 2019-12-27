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

namespace Drift\AMQP\Tests\Services;

use Bunny\Async\Client;

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
     * AService constructor.
     *
     * @param Client $usersClient
     * @param Client $ordersClient
     * @param Client $users2Client
     */
    public function __construct(
        Client $usersClient,
        Client $ordersClient,
        Client $users2Client
    )
    {
        $this->client1 = $usersClient;
        $this->client2 = $ordersClient;
        $this->client3 = $users2Client;
    }


    /**
     * Are equal.
     */
    public function areOK()
    {
        return $this->client1 !== $this->client2
            && $this->client1 === $this->client3;
    }
}
