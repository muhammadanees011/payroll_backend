<?php

namespace App\Repositories\Interfaces;

interface HMRC_RTI_EPS_Interface {

    public function message_class_get();
    public function request_body_get_xml();
    public function details_set($details);
    public function data_set($data);
    public function allowance_indicator_set($isAllowanceIndicator);

}