<?php

/*
 * This file is part of the DriftPHP Project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Drift\Websocket\Connection;

use Ratchet\ConnectionInterface;

/**
 * Class Connection.
 */
class Connection
{
    /**
     * Get connection hash.
     *
     * @param ConnectionInterface $connection
     *
     * @return string
     */
    public static function getConnectionHash(ConnectionInterface $connection): string
    {
        return substr(md5(spl_object_hash($connection)), 0, 13);
    }
}
