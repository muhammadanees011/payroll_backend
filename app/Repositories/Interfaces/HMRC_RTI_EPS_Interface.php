<?php

namespace App\Repositories\Interfaces;

interface HMRC_RTI_EPS_Interface {

    public function message_class_get();
    public function request_body_get_xml();
}