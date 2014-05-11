<?php

namespace MyConcordiaApi\Connector;

/**
 * Connector interface.
 *
 * Defines the standard interface for a connector.
 *
 * @author Alan Ly <hello@alan.ly>
 */
interface ConnectorInterface
{
    public function open();

    public function close();

    public function getConnection();
}
