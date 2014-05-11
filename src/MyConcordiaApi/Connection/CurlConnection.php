<?php

namespace MyConcordiaApi\Connection;

/**
 * CURL connection wrapper.
 *
 * A convenience and abstraction layer for the standard CURL procedural
 * calls.
 *
 * @author Alan Ly <hello@alan.ly>
 */
class CurlConnection
{
    /**
     * Specifies the user agent to present to the server when accessing.
     * The default value is derived from the current (as of writing)
     * version of Chrome running under Ubuntu 12.04.
     *
     * @var string
     */
    protected $__userAgent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.132 Safari/537.36";

    /**
     * Specifies the primary URL for the student portal.
     *
     * @var string
     */
    protected $__portalUrl = "https://www.myconcordia.ca/";

    /**
     * Defines the location where the CURL session cookies are stored.
     * Each connection instance should have a unique cookie jar.
     *
     * @var string
     */
    protected $__cookieJarPath = "";

    /**
     * Defines the length of the pseudo-random generator in bytes. In
     * highly concurrent environments, it may be advisable to increase
     * this length. For most usecases, the default value should suffice.
     *
     * @var int
     */
    protected $__randomBytesLength = 4;

    /**
     * Prepares the connection with an optionally specified cookie jar
     * container path.
     *
     * @param  string  $cookieJarContainer
     * @return void
     */
    public function open($cookieJarContainer = null)
    {
        if ($cookieJarContainer === null) {
            $cookieJarContainer = __DIR__;
        }

        $this->setCookieJarContainer($cookieJarContainer);
    }

    /**
     * Cleans up after the connection by removing the cookie jar.
     *
     * @return bool
     */
    public function close()
    {
        return unlink($__cookieJarPath);
    }

    /**
     * Perform a GET request to the specified location. Parameters can
     * be assigned as either a URL-encoded string or a keyed-array.
     * Headers are defined as array with each element as a plaintext
     * header field. The request response is returned.
     *
     * @param  string  $url
     * @param  mixed   $params
     * @param  array   $headers
     * @return string
     */
    public function get($url, $params = null, $headers = null)
    {
        return $this->curlRequest(CURLOPT_HTTPGET, $url, $params, $headers);
    }

    /**
     * Perform a POST request to the specified location. Parameters can be
     * assigned as either a URL-encoded string or a keyed-array. Headers
     * are defined as an array, with each element as a plaintext header
     * field. The request response is returned.
     *
     * @param  string  $url
     * @param  mixed   $params
     * @param  array   $headers
     * @return string
     */
    public function post($url, $params = null, $headers = null)
    {
        return $this->curlRequest(CURLOPT_POST, $url, $params, $headers);
    }

    /**
     * Override the default location for cookie jar storage. This defines
     * the containing directory path for the cookie jar. The existing
     * cookie jar will be moved to the new path. On success, the new path
     * will be returned.
     *
     * @throws \RuntimeException
     * @param  string  $path
     * @return string
     */
    public function setCookieJarContainer($path)
    {
        $randString = openssl_random_pseudo_bytes($this->__randomBytesLength);
        $hash = hash("sha256", $randString, false);

        $newCookieJarPath = (pathinfo($path))["dirname"] . DIRECTORY_SEPARATOR . $hash;

        if (is_file($newCookieJarPath)) {
            throw new \RuntimeException("Hash collision for new cookie jar: ".$newCookieJarPath);
        }

        if (is_file($this->__cookieJarPath)) {
            if (rename($this->__cookieJarPath, $newCookieJarPath) === false) {
                throw new \RuntimeException("Unable to move existing cookie jar to new path: ".$path);
            }
        }

        return $this->__cookieJarPath = $newCookieJarPath;
    }

    /**
     * Uses Curl to call the specified `target` with the appropriate 
     * parameters. Parameters can either be a URL-encoded string or a
     * keyed-array of values.
     *
     * Request method can be defined by the standard `CURLOPT_` constants,
     * such as `CURLOPT_HTTPGET` or `CURLOPT_POST`. Defaults to GET.
     *
     * @param  int    $method
     * @param  string $url
     * @param  mixed  $params
     * @param  array  $headers
     * @return string
     */
    protected function curlRequest($method, $url, $params, $headers)
    {
        $ch = curl_init();

        // Define the basic CURL options.
        $curlOpts = [
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEFILE     => $this->__cookieJarPath,
            CURLOPT_COOKIEJAR      => $this->__cookieJarPath,
            CURLOPT_REFERER        => $this->__portalUrl,
            CURLOPT_USERAGENT      => $this->__userAgent,
        ];

        // Set the request method type
        $curlOpts[$method] = true;

        /**
         * If the parameters are supplied as an array, convert it into
         * a URL-encoded string.
         */
        if (is_array($params)) {
            $params = $this->convertArrayIntoUrlString($params);
        }

        // Set the GET parameters, if appropriate
        if ($method === CURLOPT_HTTPGET && $params !== null) {
            // Append the parameters to the URL for the GET request.
            $url = $url."?".$params;
        }

        // Set the POST parameters, if appropriate
        if ($method === CURLOPT_POST && $params !== null) {
            $curlOpts[CURLOPT_POSTFIELDS] = $params;

            // Add the URL encoded specification to the header.
            if (! is_array($headers)) {
                $headers = [];
            }

            $headers[] = "Content-Type: application/x-www-form-urlencoded";
        }

        // Set the request headers
        if (is_array($headers)) {
            $curlOpts[CURLOPT_HTTPHEADER] = $headers;
        }

        // Set the request target
        $curlOpts[CURLOPT_URL] = $url;

        curl_setopt_array($ch, $curlOpts);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Takes a keyed-array and converts it into a URL-encoded string for
     * POST requests.
     *
     * @param  array  $params
     * @return string
     */
    protected function convertArrayIntoUrlString($params)
    {
        $encodedParams = [];

        foreach ($params as $key => $value) {
            $encodedParams[] = urlencode($key)."=".urlencode($value);
        }

        return implode("&", $encodedParams);
    }
}
