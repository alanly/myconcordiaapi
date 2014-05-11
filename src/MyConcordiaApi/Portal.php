<?php

namespace MyConcordiaApi;

use MyConcordiaApi\Client\AuthenticationClient;
use MyConcordiaApi\Client\TranscriptClient;
use MyConcordiaApi\Connector\PortalConnector;
use MyConcordiaApi\Parser\CourseParser;

/**
 * API Facade.
 *
 * Maintains the authentication credentials and provides the primary
 * interface for portal operations.
 *
 * @author Alan Ly <hello@alan.ly>
 */
class Portal
{
    /**
     * @var MyConcordiaApi\Connector\ConnectorInterface
     */
    protected $connector = null;

    /**
     * @param  string $netname
     * @param  string $password
     * @param  string $cookieJarContainer
     * @return void
     */
    public function __construct($cookieJarContainer = null)
    {
        $this->connector = new PortalConnector;
        $this->connector->open($cookieJarContainer);
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->connector->close();
    }

    /**
     * Authenticate to the portal with the given credentials. This
     * function should be called prior to all other operations against
     * the portal.
     *
     * @param  string  $netname
     * @param  string  $password
     * @return bool
     */
    public function authenticate($netname, $password)
    {
        $client = new AuthenticationClient($this->connector->getConnection());

        if ($client->authenticate($netname, $password) === false) {
            throw new \RuntimeException("Unable to authenticate to the portal.");
        }
    }

    /**
     * Retrieves an array of all the courses specified in the transcript.
     *
     * @return array
     */
    public function getTranscriptCourses()
    {
        $client = new TranscriptClient($this->connector->getConnection());

        $parser = new CourseParser($client->getTranscript());

        return $parser->getCourses();
    }
}
