<?php

namespace App\Repositories\Interfaces;

interface RealTimeInformationInterface {

    public function scheduleEPSSubmission($payroll); 
    public function handleEPSSubmission(); 
    public function AllowanceEPSSubmission($data); 
}