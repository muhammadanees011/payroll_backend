<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use DOMDocument;
use App\Services\Connection;
use App\Repositories\Interfaces\HMRCGatewayInterface;
use App\Repositories\Interfaces\HMRCGatewayMessageInterface;
use App\Repositories\Interfaces\HMRC_RTI_FPS_Interface;
use App\Repositories\Interfaces\HMRC_RTI_Interface;
use App\Models\RTILog;

class HMRCGatewayRepository implements HMRCGatewayInterface {

    protected $hmrcGatewayMessageRepository;
    protected $hmrc_RTI_FPS_Repository;
    protected $HMRC_RTI_Repository;

    private $log_db = NULL;
    private $log_table_sql = NULL;
    private $gateway_live = false;
    private $gateway_test = false; // aka "Test in live"
    private $gateway_url = NULL;
    private $message_class = NULL;
    private $message_transation = NULL;
    private $vendor_code = NULL;
    private $vendor_name = NULL;
    private $sender_name = NULL;
    private $sender_pass = NULL;
    private $sender_email = NULL;
    private $request_ref = NULL;
    private $response_code = NULL;
    private $response_string = NULL;
    private $response_object = NULL;
    private $response_debug = NULL;
    private $response_qualifier = NULL;
    private $response_function = NULL;
    private $response_correlation = NULL;
    private $filename = NULL;

    protected $details = [
        'year' => NULL,
        'final' => NULL,
        'currency' => 'GBP',
        'sender' => 'Employer',
        'tax_office_number' => NULL, 
        'tax_office_reference' => NULL,
    ];

    public function __construct(
    HMRCGatewayMessageInterface $hmrcGatewayMessageRepository,
    HMRC_RTI_FPS_Interface $HMRC_RTI_FPS_Repository,
    HMRC_RTI_Interface $HMRC_RTI_Repository
    )
    {
        $this->hmrcGatewayMessageRepository = $hmrcGatewayMessageRepository;
        $this->hmrc_RTI_FPS_Repository = $HMRC_RTI_FPS_Repository;
        $this->HMRC_RTI_Repository=$HMRC_RTI_Repository;
    }
    
    public function live_set($live_server, $live_run) {
        $this->gateway_live = $live_server;
        $this->gateway_test = ($live_run);
    }

    public function details_set($details) {

        $this->details = array_merge(array(
            'year' => NULL,
            'final' => NULL,
            'currency' => 'GBP',
            'sender' => 'Employer',
        ), $details);
    }

    public function filename_set($filename) {
        $this->filename = $filename;
    }

    public function filename_get() {
        return $this->filename;
    }

    public function message_keys_get() {
        return array(
            'TaxOfficeNumber' => $this->details['tax_office_number'],
            'TaxOfficeReference' => $this->details['tax_office_reference'],
        );

    }

    public function log_table_set($db, $table) {
        $this->log_db = $db;
        $this->log_table_sql = $table;
    }

    public function submission_url_get() {
        return ($this->gateway_live ? 'https://transaction-engine.tax.service.gov.uk/submission' : 'https://test-transaction-engine.tax.service.gov.uk/submission');
    }

    public function poll_url_get() {
        return ($this->gateway_live ? 'https://transaction-engine.tax.service.gov.uk/poll' : 'https://test-transaction-engine.tax.service.gov.uk/poll');
    }

    public function vendor_set($vendor_code, $vendor_name) {
        $this->vendor_code = $vendor_code;
        $this->vendor_name = $vendor_name;
    }

    public function sender_set($sender_name, $sender_pass, $sender_email) {
        $this->sender_name = $sender_name;
        $this->sender_pass = $sender_pass;
        $this->sender_email = $sender_email;
    }

    public function request_submit($request) {

        //--------------------------------------------------
        // Message class

            $this->message_class = $request->message_class_get();

            if ($this->gateway_test) {
                $this->message_class .= '-TIL';
            }

        //--------------------------------------------------
        // Setup message

            $this->gateway_url = $this->submission_url_get();
            $this->request_ref = $request;

            $body_xml = $request->request_body_get_xml();
            $message = $this->hmrcGatewayMessageRepository;
            $message->vendor_set($this->vendor_code, $this->vendor_name);
            $message->sender_set($this->sender_name, $this->sender_pass, $this->sender_email);
            $message->message_qualifier_set('request');
            $message->message_function_set('submit');
            $message->message_live_set($this->gateway_live);
            $message->message_keys_set($this->message_keys_get());
            $message->body_set_xml($body_xml);

        //--------------------------------------------------
        // Send
            $res=$this->_send($message);
            // return $res;
        //--------------------------------------------------
        // Response

            if ($this->response_qualifier == 'acknowledgement') {


                $interval = strval($this->response_object->Header->MessageDetails->ResponseEndPoint->_PollInterval);

                return array(
                        'class' => $this->message_class,
                        'correlation' => $this->response_correlation,
                        'transaction' => $this->message_transation, // Node is blank in response for some reason.
                        'endpoint' => strval($this->response_object->Header->MessageDetails->ResponseEndPoint->__text),
                        'timeout' => (time() + $interval),
                        'status' => NULL,
                        'response' => NULL,
                        'filename' => $this->filename_get(),
                    );

            } else {
                exit_with_error('Invalid response from HMRC', $this->response_debug);
            }

    }

    public function request_list($message_class) {
        //--------------------------------------------------
        // Message class

            $this->message_class = $message_class;

            if ($this->gateway_test) {
                $this->message_class .= '-TIL';
            }
        //--------------------------------------------------
        // Setup message

            $this->gateway_url = $this->submission_url_get();
            $body_xml = ''; // or could be '<IncludeIdentifiers>1</IncludeIdentifiers>'

            $message = $this->hmrcGatewayMessageRepository;
            $message->vendor_set($this->vendor_code, $this->vendor_name);
            $message->sender_set($this->sender_name, $this->sender_pass, $this->sender_email);
            $message->message_qualifier_set('request');
            $message->message_function_set('list');
            $message->message_live_set($this->gateway_live);
            $message->body_set_xml($body_xml);

        //--------------------------------------------------
        // Send
        $res=$this->_send($message);
        // return $res;

        //--------------------------------------------------
        // Extract requests

            $requests = [];

            if (isset($this->response_object->Body->StatusReport)) {
                foreach ($this->response_object->Body->StatusReport->StatusRecord as $request) {
                    // return strval($request->TransactionID);
                    $requests[] = array(
                        'class' => $this->message_class,
                        'correlation' => (!is_null($request->CorrelationID) && !empty((array) $request->CorrelationID)) ? strval($request->CorrelationID) : null,
                        'transaction' => (!is_null($request->TransactionID) && !empty((array) $request->TransactionID)) ? strval($request->TransactionID) : null,
                        'endpoint' => strval($this->response_object->Header->MessageDetails->ResponseEndPoint->__text),
                        'timeout' => time(),
                        'status' => strval($request->Status),
                        'response' => NULL,
                    );

                }
            } else {

                exit_with_error('Invalid response from HMRC', $this->response_debug);
            }

            return $requests;

    }

    public function request_poll($request, $return_error = false) {
        //--------------------------------------------------
        // Honour timeout
            $timeout = ($request['timeout'] - time());
            if ($timeout > 0) {
                sleep($timeout);
            }

        //--------------------------------------------------
        // Setup message

            $this->message_class = $request['class'];
            $this->gateway_url = $this->poll_url_get(); // $request['endpoint'] - Does not work with the values from request_list()
            $message = $this->hmrcGatewayMessageRepository;
            $message->vendor_set($this->vendor_code, $this->vendor_name);
            $message->message_qualifier_set('poll');
            $message->message_function_set('submit');
            $message->message_correlation_set($request['correlation']);
            $message->body_set_xml('');

        //--------------------------------------------------
        // Send
            $res=$this->_send($message);
            return $this->response_object;
        //--------------------------------------------------
        // Result

            if ($this->response_qualifier == 'error') {

                if ($return_error && isset($this->response_object->Body->ErrorResponse->Error->Text) && isset($this->response_object->Header->MessageDetails->CorrelationID)) {

                    return strval($this->response_object->Body->ErrorResponse->Error->Text) . ' (' . strval($this->response_object->Header->MessageDetails->CorrelationID) . ')';

                } else {

                    exit_with_error('Error from gateway "' . $this->response_object->Body->ErrorResponse->Error->Text . '"', $this->response_debug);

                }

            } else if ($this->response_qualifier == 'acknowledgement') {

                $interval = strval($this->response_object->Header->MessageDetails->ResponseEndPoint['PollInterval']);

                return array(
                        'class' => $this->message_class,
                        'correlation' => $request['correlation'],
                        'transaction' => strval($this->response_object->Header->MessageDetails->TransactionID),
                        'endpoint' => strval($this->response_object->Header->MessageDetails->ResponseEndPoint),
                        'timeout' => (time() + $interval),
                        'status' => NULL,
                        'response' => NULL,
                        'response_details' => NULL,
                    );

            } else if ($this->response_qualifier == 'response') {

                // $details = [];
                // if ($this->request_ref) {
                //     $details = $this->request_ref->response_details($this->response_object);
                // }

                return array(
                        'class' => $this->message_class,
                        'correlation' => $request['correlation'],
                        'transaction' => strval($this->response_object->Header->MessageDetails->TransactionID),
                        'endpoint' => strval($this->response_object->Header->MessageDetails->ResponseEndPoint->__text),
                        'timeout' => time(),
                        'status' => 'SUBMISSION_RESPONSE',
                        'response' => $this->response_string,
                        'response_details' => $this->response_object->Header->MessageDetails,
                        'filename' => $this->filename_get(),
                    );

            } else {

                exit_with_error('Invalid qualifier from HMRC', $this->response_debug);

            }

    }

    public function request_delete($request) {

        //--------------------------------------------------
        // Setup message

        $this->message_class = $request['class'];
        $this->gateway_url = $this->submission_url_get();

        $message = $this->hmrcGatewayMessageRepository;
        $message->vendor_set($this->vendor_code, $this->vendor_name);
        $message->message_qualifier_set('request');
        $message->message_function_set('delete');
        $message->message_live_set($this->gateway_live);
        $message->message_correlation_set($request['correlation']);

        //--------------------------------------------------
        // Send

        $this->_send($message);
        // return $this->response_object;
        //--------------------------------------------------
        // Verify

        if ($this->response_correlation != $request['correlation']) {
            exit_with_error('Did not delete correlation "' . $request['correlation'] . '"', $this->response_debug);
        }

    }

    public function response_debug_get() {
        return $this->response_debug;
    }

    public function _send($message) {
        //--------------------------------------------------
        // Message details

        $this->message_transation = str_replace('.', '', microtime(true)); // uniqid();

        $message->message_class_set($this->message_class);
        $message->message_transation_set($this->message_transation);

        $message_xml = $message->xml_get();
        $message_correlation = $message->message_correlation_get();

        //--------------------------------------------------
        // IRMark
        // return $message_xml;
        if (preg_match('/(<IRmark Type="generic">)[^<]*(<\/IRmark>)/', $message_xml, $matches)) {

            $message_xml_clean = str_replace($matches[0], '', $message_xml);

            if (preg_match('/<GovTalkMessage( xmlns="[^"]+")>/', $message_xml, $namespace_matches)) {
                $message_namespace = $namespace_matches[1];
            } else {
                $message_namespace = '';
            }
            $message_xml_clean = preg_replace('/^.*<Body>(.*)<\/Body>.*$/s', '<Body' . $message_namespace . '>$1</Body>', $message_xml_clean);
            // return $message_xml_clean;
            $message_xml_dom = new DOMDocument;
            $message_xml_dom->loadXML($message_xml_clean);
            $message_irmark = base64_encode(sha1($message_xml_dom->documentElement->C14N(),true));
            $message_xml = str_replace($matches[0], $matches[1] . $message_irmark . $matches[2], $message_xml);
            // return $message_xml;

        } else {

            $message_irmark = NULL;

        }
        //--------------------------------------------------
        // Validation

        if (false) {
            $xsi_path = dirname(__FILE__) . '/' . $xsi_path;

            $validate_xml = $message->body_get_xml();
            // $validate_xml = $message->xml_get();

            $validate = new DOMDocument();
            $validate->loadXML($validate_xml);

            if (!$validate->schemaValidate($xsi_path)) {
                exit_with_error('Invalid XML according to XSI file', $validate_xml);
            }

        }

        //--------------------------------------------------
        // return $message_xml_clean;
        // Log create
        $log=new RTILog();
        $log->request_url=$this->gateway_url;
        $log->request_xml= $message_xml;
        $log->request_correlation=strval($message_correlation);
        $log->request_irmark=strval($message_irmark);
        $log->save();

        //--------------------------------------------------
        // Setup connection - similar to curl

        $connection = new connection();
        $connection->timeout_set(15);
        $connection->exit_on_error_set(false);
        $connection->header_set('Content-Type', 'application/xml');

        //--------------------------------------------------
        // Send request

        header('Content-Type: text/xml; charset=UTF-8');
        // exit($message_xml);
        
        $dom = new DOMDocument();
        $dom->loadXML($message_xml);        
        $message_xml = $dom->saveXML();
        $timestamp = date('Ymd_His'); // Format: YYYYMMDD_HHMMSS
        $randomString = bin2hex(random_bytes(5)); // Generate a random 5-byte (10-character) string
        $message_repo = $this->hmrcGatewayMessageRepository;
        $message_qualifier=$message_repo->message_qualifier_get();

        if($message_qualifier == 'request' && ($this->message_class == 'HMRC-PAYE-RTI-FPS-TIL' || $this->message_class == 'HMRC-PAYE-RTI-FPS')){
            $filename="xml/fps/fps_submitted_{$timestamp}_{$randomString}.xml";
            $dom->save($filename);
            $this->filename_set($filename);
        }else if($message_qualifier == 'request' && ($this->message_class == 'HMRC-PAYE-RTI-EPS-TIL' || $this->message_class == 'HMRC-PAYE-RTI-EPS')){
            $filename="xml/eps/eps_submitted_{$timestamp}_{$randomString}.xml";
            $dom->save($filename);
            $this->filename_set($filename);
        }

        // echo $message_xml;
        // dd($message_xml);

        $send_result = $connection->post($this->gateway_url, $message_xml);
        // return $send_result;
        if (!$send_result) {
            exit_with_error('Could not connect to HMRC', $connection->error_message_get() . "\n\n" . $connection->error_details_get());
        }
        if ($connection->response_code_get() != 200) {
            exit_with_error('Invalid HTTP response from HMRC', $connection->response_full_get());
        }
        $this->response_string = $connection->response_data_get();

        $xml = new \SimpleXMLElement($this->response_string);
        $res_data= $this->parseXmlToObject($xml);
        $this->response_object= $res_data;

        $this->response_debug = $this->gateway_url . "\n\n" . $message_xml . "\n\n" . $this->response_string;
        

        //--------------------------------------------------
        // Update log
        $log->response_xml=$this->response_string;
        $log->save();

        //--------------------------------------------------
        // Extract details

        // return $this->response_object;
        if (isset($this->response_object->Header->MessageDetails->Qualifier)) {
            
            $this->response_qualifier = strval($this->response_object->Header->MessageDetails->Qualifier);
        } else {
            exit_with_error('Invalid response from HMRC (qualifier)', $this->response_debug);
        }

        if (isset($this->response_object->Header->MessageDetails->Function)) {
            $this->response_function = strval($this->response_object->Header->MessageDetails->Function);
        } else {
            exit_with_error('Invalid response from HMRC (function)', $this->response_debug);
        }


        if (isset($this->response_object->Header->MessageDetails->CorrelationID)) {
            if (empty((array) $this->response_object->Header->MessageDetails->CorrelationID)) {
                $this->response_correlation = null;
            }else{
                $this->response_correlation = strval($this->response_object->Header->MessageDetails->CorrelationID);
            }
        } else {
            exit_with_error('Invalid response from HMRC (correlation)', $this->response_debug);
        }

        //--------------------------------------------------
        // Update log (additional details)
        $log->response_qualifier=$this->response_qualifier;
        $log->response_function=$this->response_function;
        $log->response_correlation=$this->response_correlation;
        $log->save();

        //---------------------SAVE RESPONSE XML--------------------------
        if($this->response_qualifier == 'response'){
            $dom = new DOMDocument();
            $dom->loadXML($this->response_string);        
            $dom->saveXML();
            $timestamp = date('Ymd_His'); // Format: YYYYMMDD_HHMMSS
            $randomString = bin2hex(random_bytes(5)); // Generate a random 5-byte (10-character) string
            $message_repo = $this->hmrcGatewayMessageRepository;
            $message_qualifier=$message_repo->message_qualifier_get();

            if($message_qualifier == 'poll' && ($this->message_class == 'HMRC-PAYE-RTI-FPS-TIL' || $this->message_class == 'HMRC-PAYE-RTI-FPS')){
                $filename="xml/fps/fps_response_{$timestamp}_{$randomString}.xml";
                $dom->save($filename);
                $this->filename_set($filename);
            }else if($message_qualifier == 'poll' && ($this->message_class == 'HMRC-PAYE-RTI-EPS-TIL' || $this->message_class == 'HMRC-PAYE-RTI-EPS')){
                $filename="xml/eps/eps_response_{$timestamp}_{$randomString}.xml";
                $dom->save($filename);
                $this->filename_set($filename);
            }
        }

        //--------------------------------------------------
        // Check correlation

        if ($message_correlation !== NULL && $this->response_correlation != $message_correlation) {
            exit_with_error('Invalid response correlation "' . $message_correlation . '"', $this->response_debug);
        }

    }

    // function parseXmlToObject($xml)
    // {
    //     $result = new \stdClass();

    //     // Add attributes as properties
    //     foreach ($xml->attributes() as $key => $value) {
    //         $result->{"_$key"} = (string)$value;
    //     }

    //     // Add children elements
    //     foreach ($xml->children() as $key => $child) {
    //         $parsedChild = $this->parseXmlToObject($child);

    //         // If multiple children with the same key, convert to array
    //         if (isset($result->$key)) {
    //             if (!is_array($result->$key)) {
    //                 $result->$key = [$result->$key];
    //             }
    //             $result->{$key}[] = $parsedChild;
    //         } else {
    //             $result->$key = $parsedChild;
    //         }
    //     }

    //     // Add the text content
    //     $textContent = trim((string)$xml);
    //     if (!empty($textContent)) {
    //         $result->__text = $textContent;
    //     }

    //     return $result;
    // }

    function parseXmlToObject($xml)
    {
        $result = new \stdClass();
        // Add attributes as properties
        foreach ($xml->attributes() as $key => $value) {
            $result->{"_$key"} = (string)$value;
        }
        // Add children elements
        foreach ($xml->children() as $key => $child) {
            $parsedChild = $this->parseXmlToObject($child);
            // If multiple children with the same key, convert to array
            if (isset($result->$key)) {
                if (!is_array($result->$key)) {
                    $result->$key = [$result->$key];
                }
                $result->{$key}[] = $parsedChild;
            } else {
                $result->$key = $parsedChild;
            }
        }
        // Add the text content
        $textContent = trim((string)$xml);
        if (!empty($textContent)) {
            // If there are attributes or multiple children, use "__text"
            if (count((array)$result) > 0) {
                $result->__text = $textContent;
            } else {
                // If it's a simple text-only node, return the text directly
                return $textContent;
            }
        }
        // If there is only one child element and no attributes, return it directly
        if (count((array)$result) === 1 && isset($result->__text)) {
            return $result->__text;
        }

        return $result;
    }

    
}