<?php

namespace MyConcordiaApi\Client;

use MyConcordiaApi\Connection\CurlConnection;

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
     * Specifies the URL for the student record/transcript page. The 
     * default value is a link which (as of writing) redirects to the
     * transcript page with a persistent token code, once authenticated.
     *
     * @var string
     */
    protected $__srUrl = "https://my.concordia.ca/psc/portprod/EMPLOYEE/EMPL/s/WEBLIB_CONCORD.CU_SIS_INFO.FieldFormula.IScript_UnOfficialRecord?FolderPath=PORTAL_ROOT_OBJECT.CU_ACADEMIC.CU_UNOFFICIALRECORD&IsFolder=false&IgnoreParamTempl=FolderPath%2cIsFolder&PortalActualURL=https%3a%2f%2fmy.concordia.ca%2fpsc%2fportprod%2fEMPLOYEE%2fEMPL%2fs%2fWEBLIB_CONCORD.CU_SIS_INFO.FieldFormula.IScript_UnOfficialRecord&PortalContentURL=https%3a%2f%2fmy.concordia.ca%2fpsc%2fportprod%2fEMPLOYEE%2fEMPL%2fs%2fWEBLIB_CONCORD.CU_SIS_INFO.FieldFormula.IScript_UnOfficialRecord&PortalContentProvider=EMPL&PortalCRefLabel=Student%20Record&PortalRegistryName=EMPLOYEE&PortalServletURI=https%3a%2f%2fmy.concordia.ca%2fpsp%2fportprod%2f&PortalURI=https%3a%2f%2fmy.concordia.ca%2fpsc%2fportprod%2f&PortalHostNode=EMPL&NoCrumbs=yes";


    /**
     * @var MyConcordiaApi\Connection\CurlConnection
     */
    protected $connection = null;

    /**
     * @param MyConcordiaApi\Connection\CurlConnection  $connection
     */
    public function __construct(CurlConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Retrieves the transcript page as a `DOMDocument` object that can 
     * then be parsed, via an authenticated connection.
     *
     * @throws \RuntimeException
     * @return \DOMDocument
     */
    public function getTranscript()
    {
        // Retrieve the student record
        $response = $this->connection->get($this->__srUrl);

        // Verify that the response is of the student record.
        if (stripos($response, "Student Record") === false) {
            throw new \RuntimeException("Unable to retrieve the student record. Response follows: ".$response);
        }

        // Hide libxml warnings because Concordia uses invalid HTML.
        libxml_use_internal_errors(true);

        // Create the new DOCDocument
        $domDoc = new \DOMDocument;
        $domDoc->loadHTML($response);

        return $domDoc;
    }
}
