<?php

namespace App\Repositories\Interfaces;

interface HMRC_RTI_FPS_Interface {

    public function message_class_get();
    public function details_set($details);
    public function employee_add($employees);
    public function request_body_get_xml();
}