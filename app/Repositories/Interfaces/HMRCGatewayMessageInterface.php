<?php

namespace App\Repositories\Interfaces;

interface HMRCGatewayMessageInterface {

    public function message_live_set($message_live);
    public function message_class_set($message_class);
    public function message_qualifier_set($message_qualifier);
    public function message_function_set($message_function);
    public function message_transation_set($message_transation);
    public function message_correlation_set($message_correlation);
    public function message_correlation_get();
    public function message_keys_set($message_keys);
    public function vendor_set($vendor_code,$vendor_name);
    public function sender_set($sender_name,$sender_pass,$sender_email);
    public function body_set_xml($body_xml);
    public function body_get_xml();
    public function xml_get();
    
}