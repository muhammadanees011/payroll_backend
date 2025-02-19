<?php

namespace App\Repositories;
use App\Repositories\Interfaces\RealTimeInformationInterface;
use App\Repositories\Interfaces\HMRCGatewayInterface;
use App\Repositories\Interfaces\HMRC_RTI_EPS_Interface;
use App\Services\NICCalculator;
use App\Models\AutomaticEPSSubmission;
use App\Models\EPSSubmission;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RealTimeInformationRepository implements RealTimeInformationInterface {

    protected $nicCalculator;
    private $hmrcGatewayRepository;
    private $HMRC_RTI_EPS_Repository;

    private $config_sender_name = 'ISV635';
	private $config_sender_pass = 'fGuR34fAOEJf';
	private $config_sender_email = 'muhammad.anees@xepos.co.uk';
	private $config_tax_office_number = '635';
	private $config_tax_office_reference = 'A635';
	private $config_accounts_office_reference = '120PA01793429';
	private $config_corporation_tax_reference = '1111111111';

    public function __construct(NICCalculator $nicCalculator,
     HMRCGatewayInterface $hmrcGatewayRepository,
     HMRC_RTI_EPS_Interface $HMRC_RTI_EPS_Repository
     )
    {
        $this->nicCalculator = $nicCalculator;
        $this->hmrcGatewayRepository = $hmrcGatewayRepository;
        $this->HMRC_RTI_EPS_Repository = $HMRC_RTI_EPS_Repository;
    }

    public function scheduleEPSSubmission($payroll){
        $scheduleEPS = new AutomaticEPSSubmission();  

        $scheduleEPS->payroll_id = $payroll->id;
        $scheduleEPS->pay_schedule_id = $payroll->pay_schedule_id;
        $scheduleEPS->tax_period = $payroll->tax_period;
        $scheduleEPS->pay_run_start_date = $payroll->pay_run_start_date;
        $scheduleEPS->pay_run_end_date = $payroll->pay_run_end_date;

        $scheduleEPS->statutory_maternity_pay = $payroll->statutory_maternity_pay;
        $scheduleEPS->statutory_paternity_pay = $payroll->statutory_paternity_pay;
        $scheduleEPS->statutory_adoption_pay = $payroll->statutory_adoption_pay;
        $scheduleEPS->statutory_shared_parental_pay = $payroll->statutory_shared_parental_pay;
        $scheduleEPS->statutory_parental_bereavement_pay = $payroll->statutory_parental_bereavement_pay;

        $scheduleEPS->nic_compensation_on_smp = $this->nicCalculator->calculateNicCompensationOnStatutory($payroll->statutory_maternity_pay);
        $scheduleEPS->nic_compensation_on_spp = $this->nicCalculator->calculateNicCompensationOnStatutory($payroll->statutory_paternity_pay);
        $scheduleEPS->nic_compensation_on_sap = $this->nicCalculator->calculateNicCompensationOnStatutory($payroll->statutory_adoption_pay);
        $scheduleEPS->nic_compensation_on_shpp = $this->nicCalculator->calculateNicCompensationOnStatutory($payroll->statutory_shared_parental_pay);
        $scheduleEPS->nic_compensation_on_spbp = $this->nicCalculator->calculateNicCompensationOnStatutory($payroll->statutory_parental_bereavement_pay);

        $scheduleEPS->cis_deduction_suffered = $payroll->cis_deduction;

        $scheduleEPS->status = 'Pending';
        $scheduleEPS->save();

    }

    public function handleEPSSubmission(){
        $eps_data=AutomaticEPSSubmission::where('status','Pending')->get();
        foreach($eps_data as $data){
            return $this->submitScheduledEPS($data->payroll_id);
        }

    }

    public function submitScheduledEPS($payroll_id){
        $eps_data = $this->getScheduledEPSData($payroll_id);
        $hmrc_gateway = $this->hmrcGatewayRepository;
        $hmrc_gateway->live_set(false, true); //for live it is false and for test it is true
        // $hmrc_gateway->log_table_set($db, DB_PREFIX . 'table_name');
        $hmrc_gateway->vendor_set('9136', 'XEPayroll');
        $hmrc_gateway->sender_set($this->config_sender_name, $this->config_sender_pass, $this->config_sender_email);
        $final = false; // e.g. ($payment_month == 12)

        // Create request
        $this->hmrcGatewayRepository->details_set([
            'year' => 2024,
            'final' => $final,
            'tax_office_number' => $this->config_tax_office_number,
            'tax_office_reference' => $this->config_tax_office_reference,
            'accounts_office_reference' => $this->config_accounts_office_reference,
            'corporation_tax_reference' => $this->config_corporation_tax_reference,
        ]);

        $hmrc_eps = $this->HMRC_RTI_EPS_Repository; // Employer Payment Summary

        $hmrc_eps->details_set([
            'year' => 2024,
            'final' => $final,
            'tax_office_number' => $this->config_tax_office_number,
            'tax_office_reference' => $this->config_tax_office_reference,
            'accounts_office_reference' => $this->config_accounts_office_reference,
            'corporation_tax_reference' => $this->config_corporation_tax_reference,
        ]);


        $hmrc_eps->data_set($eps_data);
        // return $hmrc_eps->request_body_get_xml();

        //--------------------------------------------------
        // Send and poll for response

        $request_submit = $hmrc_gateway->request_submit($hmrc_eps);
        // return $request_submit;

        $EPSSubmission=new EPSSubmission();
        $EPSSubmission->type='Recoverable Amounts';
        $EPSSubmission->status='Accepted';
        $EPSSubmission->tax_year=$this->getCurrentTaxYear();
        $EPSSubmission->tax_month=$eps_data['tax_period'];
        $currentDate = Carbon::today();
        $EPSSubmission->submission_date=$currentDate;
        $EPSSubmission->submission_xml=$request_submit['filename'];
        $EPSSubmission->save();

        $k = 0;

        while ($request_submit['status'] === NULL && $k++ < 5) {
            $request_submit = $hmrc_gateway->request_poll($request_submit);
            // print_r($request_submit);
            // return $request_submit;
            if ($request_submit && isset($request_submit->Body->SuccessResponse)) {
                $eps_status=true;
                return [
                    'request' => $request_submit,
                    'eps_submission_status'  => $eps_status
                ];
            }else if ($request_submit && isset($request_submit['response'])) {
                $eps_status=true;
                $EPSSubmission->response_xml=$request_submit['filename'];
                $EPSSubmission->save();
                return [
                    'request' => $request_submit,
                    'eps_submission_status'  => $eps_status
                ];
            }
        }

        $eps_submission_status=false;
        return $eps_submission_status;
        // return $request_submit;
        if ($request_submit['status'] === NULL) {
            exit('Stopped waiting for a response after several attempts.');
        }

        //--------------------------------------------------
        // Delete request

        $hmrc_gateway->request_delete($request_submit);

    }

    public function getScheduledEPSData($payroll_id){
        $eps_data=AutomaticEPSSubmission::where('payroll_id',$payroll_id)->first();
        $eps_data=[
            'tax_period' => $eps_data->tax_period,
            'SSP_Recovered' => number_format((float)$eps_data->statutory_sick_pay, 2, '.', ''),
            'SMP_Recovered' => number_format((float)$eps_data->statutory_maternity_pay, 2, '.', ''),
            'SPP_Recovered' => number_format((float)$eps_data->statutory_paternity_pay, 2, '.', ''),
            'SAP_Recovered' => number_format((float)$eps_data->statutory_adoption_pay, 2, '.', ''),
            'ShPP_Recovered' => number_format((float)$eps_data->statutory_shared_parental_pay, 2, '.', ''),
            'NIC_CompensationOnSMP' => number_format((float)$eps_data->nic_compensation_on_smp, 2, '.', ''),
            'NIC_CompensationOnSPP' => number_format((float)$eps_data->nic_compensation_on_spp, 2, '.', ''),
            'NIC_CompensationOnSAP' => number_format((float)$eps_data->nic_compensation_on_sap, 2, '.', ''),
            'NIC_CompensationOnShPP' => number_format((float)$eps_data->nic_compensation_on_shpp, 2, '.', ''),
            'CIS_DeductionsSuffered' => number_format((float)$eps_data->nic_compensation_on_spbp, 2, '.', ''),
            'NIC_sHoliday' => '0.00',
            'final' =>[
                'free_of_tax_payments' => '0.00',
                'expenses_and_benefits' => '0.00',
                'employees_out_of_uk' => '0.00',
                'employees_pay_to_third_party' => '0.00',
                'p11d_forms_due' => '0.00',
                'service_company' => '0.00'
            ]    
        ];

        return $eps_data;
    }

    public function getCurrentTaxYear() {
        $currentDate = Carbon::now();
        $year = $currentDate->year;
        // If today is before April 6th, tax year started last year
        if ($currentDate->month < 4 || ($currentDate->month == 4 && $currentDate->day < 6)) {
            $year -= 1;
        }
        // Format as "23 - 24" (last two digits of the year)
        return substr($year, -2) . ' - ' . substr($year + 1, -2);
    }


    public function AllowanceEPSSubmission($isAllowanceIndicator){
        $hmrc_gateway = $this->hmrcGatewayRepository;
        $hmrc_gateway->live_set(false, true); //for live it is false and for test it is true
        // $hmrc_gateway->log_table_set($db, DB_PREFIX . 'table_name');
        $hmrc_gateway->vendor_set('9136', 'XEPayroll');
        $hmrc_gateway->sender_set($this->config_sender_name, $this->config_sender_pass, $this->config_sender_email);
        $final = false; // e.g. ($payment_month == 12)

        // Create request
        $this->hmrcGatewayRepository->details_set([
            'year' => 2024,
            'final' => $final,
            'tax_office_number' => $this->config_tax_office_number,
            'tax_office_reference' => $this->config_tax_office_reference,
            'accounts_office_reference' => $this->config_accounts_office_reference,
            'corporation_tax_reference' => $this->config_corporation_tax_reference,
        ]);

        $hmrc_eps = $this->HMRC_RTI_EPS_Repository; // Employer Payment Summary

        $hmrc_eps->details_set([
            'year' => 2024,
            'final' => $final,
            'tax_office_number' => $this->config_tax_office_number,
            'tax_office_reference' => $this->config_tax_office_reference,
            'accounts_office_reference' => $this->config_accounts_office_reference,
            'corporation_tax_reference' => $this->config_corporation_tax_reference,
        ]);

        $hmrc_eps->allowance_indicator_set($isAllowanceIndicator);
        $hmrc_eps->data_set(null);
        // return $hmrc_eps->request_body_get_xml();

        //--------------------------------------------------
        // Send and poll for response

        $request_submit = $hmrc_gateway->request_submit($hmrc_eps);
        // return $request_submit;
        $k = 0;

        while ($request_submit['status'] === NULL && $k++ < 5) {
            $request_submit = $hmrc_gateway->request_poll($request_submit);
            // print_r($request_submit);
            return $request_submit;
            if ($request_submit && isset($request_submit->Body->SuccessResponse)) {
                $eps_status=true;
                return [
                    'request' => $request_submit,
                    'eps_submission_status'  => $eps_status
                ];
            }else if ($request_submit && isset($request_submit['response'])) {
                $eps_status=true;
                return [
                    'request' => $request_submit,
                    'eps_submission_status'  => $eps_status
                ];
            }
        }

        $eps_submission_status=false;
        return $eps_submission_status;
        // return $request_submit;
        if ($request_submit['status'] === NULL) {
            exit('Stopped waiting for a response after several attempts.');
        }

        //--------------------------------------------------
        // Delete request

        $hmrc_gateway->request_delete($request_submit);

    }

}