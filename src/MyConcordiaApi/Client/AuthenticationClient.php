<?php

namespace MyConcordiaApi\Client;

use MyConcordiaApi\Connection\CurlConnection;

/**
 * Portal authentication client.
 *
 * Handles authentication for a specific connection instance.
 *
 * @author Alan Ly <hello@alan.ly>
 */
class AuthenticationClient
{
    /**
     * Specifies the URL for the login request.
     *
     * @var string
     */
    protected $__loginUrl  = "https://my.concordia.ca/psp/portprod/?cmd=login&languageCd=ENG";

    /**
     * @var MyConcordiaApi\Connection\CurlConnection
     */
    protected $connection = null;

    /**
     * @param MyConcordiaApi\Connection\CurlConnection
     */
    public function __construct(CurlConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Authenticates to the portal via the instance connection. The
     * `netname` is the user name used to log into the student portal
     * with the associated password. Returns `true` on success.
     *
     * @param  string  $netname
     * @param  string  $password
     * @return bool
     */
    public function authenticate($netname, $password)
    {
        // Define the POST fields required for authentication.
        $postFields = [
            "resource"  => "/content/cspace/en/login.html",
            "_charset_" => "UTF-8",
            "userid"    => $netname,
            "pwd"       => $password,
        ];

        // Call POST request to login URL via the opened connection.
        $response = $this->connection->post($this->__loginUrl, $postFields)

        // Return the success of the authentication attempt.
        return (stripos("MyConcordia Sign-in", $response) !== false);
    }
}
