<?php

namespace MyConcordiaApi\Client;

/**
 * Portal-transcript client.
 *
 * Responsible for appropriately retrieving the transcript document.
 *
 * @author Alan Ly <hello@alan.ly>
 */
class TranscriptClient
{
    /**
     * Specifies the primary URL for the student portal.
     *
     * @var string
     */
    private $__portalUrl = "https://www.myconcordia.ca/";

    /**
     * Specifies the URL for the login request.
     *
     * @var string
     */
    private $__loginUrl  = "https://my.concordia.ca/psp/portprod/?cmd=login&languageCd=ENG";

    /**
     * Specifies the URL for the student record/transcript page. The 
     * default value is a link which (as of writing) redirects to the
     * transcript page with a persistent token code, once authenticated.
     *
     * @var string
     */
    private $__srUrl     = "https://my.concordia.ca/psc/portprod/EMPLOYEE/EMPL/s/WEBLIB_CONCORD.CU_SIS_INFO.FieldFormula.IScript_UnOfficialRecord?FolderPath=PORTAL_ROOT_OBJECT.CU_ACADEMIC.CU_UNOFFICIALRECORD&IsFolder=false&IgnoreParamTempl=FolderPath%2cIsFolder&PortalActualURL=https%3a%2f%2fmy.concordia.ca%2fpsc%2fportprod%2fEMPLOYEE%2fEMPL%2fs%2fWEBLIB_CONCORD.CU_SIS_INFO.FieldFormula.IScript_UnOfficialRecord&PortalContentURL=https%3a%2f%2fmy.concordia.ca%2fpsc%2fportprod%2fEMPLOYEE%2fEMPL%2fs%2fWEBLIB_CONCORD.CU_SIS_INFO.FieldFormula.IScript_UnOfficialRecord&PortalContentProvider=EMPL&PortalCRefLabel=Student%20Record&PortalRegistryName=EMPLOYEE&PortalServletURI=https%3a%2f%2fmy.concordia.ca%2fpsp%2fportprod%2f&PortalURI=https%3a%2f%2fmy.concordia.ca%2fpsc%2fportprod%2f&PortalHostNode=EMPL&NoCrumbs=yes";

    /**
     * Specifies the user agent to present to the server when accessing.
     * The default value is derived from the current (as of writing)
     * version of Chrome running under Ubuntu 12.04.
     *
     * @var string
     */
    private $__userAgent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.132 Safari/537.36";

    /**
     * Instance variable containing the student netname or username
     * required to authenticate to the MyConcordia portal.
     *
     * @var string
     */
    protected $netname = "";

    /**
     * Instance variable containing the account password required
     * to authenticate to the portal.
     *
     * @var string
     */
    protected $password = "";

    /**
     * Defines a temporary location for storing cookie data.
     *
     * @var string
     */
    protected $cookieJar = "";

    /**
     * @param string $netname
     * @param string $password
     * @param string $cookieJar
     */
    public function __construct($netname = "", $password = "", $cookieJar = "")
    {
        $this->netname = $netname;
        $this->password = $password;
        $this->cookieJar = $cookieJar;
    }

    /**
     * Retrieves the transcript page as a `DOMDocument` object that can 
     * then be parsed.
     *
     * @return \DOMDocument
     */
    public function getTranscript()
    {
        // Create URL encoded POST field
        $postfields = "resource=%2Fcontent%2Fcspace%2Fen%2Flogin.html&_charset_=UTF-8&userid=".urlencode($this->netname)."&pwd=".urlencode($this->password);

        // Authenticate the user
        $response = $this->callCurl(CURLOPT_POST, $this->__loginUrl, $postfields, true);

        if (stripos("MyConcordia Sign-in", $response) !== false) {
            throw new \RuntimeException("Unable to login. Response content follows:\n".$response);
        }

        // Retrieve the student record
        $response = $this->callCurl(CURLOPT_HTTPGET, $this->__srUrl, "", "");

        // Remove the cookie jar.
        unlink($this->cookieJar);

        /**
         * Hide libxml warnings because Concordia uses invalid HTML.
         */
        libxml_use_internal_errors(true);

        // Create the new DOCDocument
        $domDoc = new \DOMDocument;
        $domDoc->loadHTML($response);

        return $domDoc;
    }

    /**
     * Uses Curl to call the specified `target` with the
     * appropriate parameters.
     *
     * Request method can be defined by the standard `CURLOPT_` constants,
     * such as `CURLOPT_HTTPGET` or `CURLOPT_POST`. Defaults to GET.
     *
     * @param  int    $method
     * @param  string $target
     * @param  mixed  $postfields
     * @param  bool   $isUrlEncoded
     * @return string
     */
    protected function callCurl($method = CURLOPT_HTTPGET, $target, $postfields = "", $isUrlEncoded = false)
    {
        $ch = curl_init();

        $curlOpts = [
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => false,
            $method                => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEFILE     => $this->cookieJar,
            CURLOPT_COOKIEJAR      => $this->cookieJar,
            CURLOPT_REFERER        => $this->__portalUrl,
            CURLOPT_USERAGENT      => $this->__userAgent,
            CURLOPT_URL            => $target,
        ];

        if ($method === CURLOPT_POST) {
            $curlOpts[CURLOPT_POSTFIELDS] = $postfields;

            if ($isUrlEncoded === true) {
                $curlOpts[CURLOPT_HTTPHEADER] = ['Content-Type: application/x-www-form-urlencoded'];
            }
        }

        curl_setopt_array($ch, $curlOpts);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
