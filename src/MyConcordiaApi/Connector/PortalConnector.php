<?php

namespace MyConcordiaApi\Connector;

use MyConcordiaApi\Connection\CurlConnection;

/**
 * Portal connection manager.
 *
 * Handles the connection to the portal.
 *
 * @author Alan Ly <hello@alan.ly>
 */
class PortalConnector implements ConnectorInterface
{
    /**
     * @var MyConcordiaApi\Connection\CurlConnection
     */
    protected $connection = null;

    /**
     * Create a connection to the portal.
     *
     * @param  string  $cookieJarContainer
     * @return MyConcordiaApi\Connection\CurlConnection
     */
    public function open($cookieJarContainer = null)
    {
        $this->connection = new CurlConnection;

        $this->connection->open($cookieJarContainer);

        return $this->connection;
    }

    /**
     * Close an existing connection to the portal.
     *
     * @throws \RuntimeException
     * @return bool
     */
    public function close()
    {
        if ($this->connection === null) {
            throw new \RuntimeException("CurlConnection is not instantiated; unable to close.");
        }

        if ($this->connection->close() === false) {
            throw new \RuntimeException("Unable to close CurlConnection.");
        }

        $this->connection = null;

        return true;
    }

    /**
     * Get the connection handler.
     *
     * @return MyConcordiaApi\Connection\CurlConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
