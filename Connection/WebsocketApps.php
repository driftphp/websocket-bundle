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

/**
 * Class WebsocketApps.
 */
class WebsocketApps
{
    private array $apps = [];

    /**
     * Add app.
     *
     * @param string       $name
     * @param WebsocketApp $app
     * @param array        $configuration
     */
    public function addApp(
        string $name,
        WebsocketApp $app,
        array $configuration
    ) {
        $this->apps[$name] = [$app, $configuration];
    }

    /**
     * Get by names.
     *
     * @param string[] $names
     *
     * @return array
     */
    public function getByNames(array $names): array
    {
        return array_intersect_key($this->apps, array_flip($names));
    }
}
