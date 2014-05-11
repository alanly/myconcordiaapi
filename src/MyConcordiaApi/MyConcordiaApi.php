<?php

namespace MyConcordiaApi;

use MyConcordiaApi\Client\TranscriptClient;
use MyConcordiaApi\Parser\CourseParser;

/**
 * API Facade.
 *
 * Maintains the authentication credentials and provides the primary
 * interface for portal operations.
 *
 * @author Alan Ly <hello@alan.ly>
 */
class MyConcordiaApi
{
    /**
     * @var string
     */
    protected $netname = "";

    /**
     * @var string
     */
    protected $password = "";

    /**
     * @var string
     */
    protected $cookieJar = __DIR__."/cookiejar.txt";

    /**
     * @param string $netname
     * @param string $password
     * @param string $cookieJar
     */
    public function __construct($netname = "", $password = "", $cookieJar = "")
    {
        parent::construct();

        $this->netname = $netname;
        $this->password = $password;

        if ($cookieJar !== "") {
            $this->cookieJar = $cookieJar;
        }
    }

    /**
     * Retrieves an array of all the courses specified in the transcript.
     *
     * @return array
     */
    public function getTranscriptCourses()
    {
        $client = new TranscriptClient($this->netname, $this->password, $this->cookieJar);

        $parser = new CourseParser($client->getTranscript());

        return $parser->getCourses();
    }
}
