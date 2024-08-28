<?php

namespace App\Repositories\Interfaces;

interface HMRC_RTI_Interface {

    public function details_set($details);
    public function message_keys_get();
    public function request_header_get_xml();
    public function response_details($response_object);    
}