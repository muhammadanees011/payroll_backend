<?php

namespace App\Repositories\Interfaces;

interface HMRC_VAT_Interface {

    public function details_set($details);
    public function message_keys_get();
    public function request_header_get_xml();
    public function message_class_get();
    public function request_body_get_xml();
    public function format_amount($decimals, $amount);
    public function response_details($response_object);
    
}