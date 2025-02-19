<?php

namespace App\Repositories;

use App\Repositories\Interfaces\HMRC_RTI_EPS_Interface;
use App\Repositories\Interfaces\HMRC_RTI_Interface;

class HMRC_RTI_EPS_Repository implements HMRC_RTI_EPS_Interface {
    private $employees = [];
    private $details;
    private $eps_data;
    private $allowance_indicator=null;

    public function message_class_get() {
        return 'HMRC-PAYE-RTI-EPS';
    }

    public function __construct(HMRC_RTI_Interface $HMRC_RTI_Repository) {
        $this->HMRC_RTI_Repository=$HMRC_RTI_Repository;
    }

    public function details_set($details){
        $this->details=$details;
        $this->HMRC_RTI_Repository->details_set($details);
    }

    public function data_set($data){
        $this->eps_data=$data;
    }

    public function allowance_indicator_set($isAllowanceIndicator){
        $this->allowance_indicator=$isAllowanceIndicator;
    }

    public function request_body_get_xml() {

        if ($this->details['year'] == 2013) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/13-14/2';
        } else if ($this->details['year'] == 2014) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/14-15/1';
        } else if ($this->details['year'] == 2015) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/15-16/1';
        } else if ($this->details['year'] == 2016) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/16-17/1';
        } else if ($this->details['year'] == 2017) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/17-18/1';
        } else if ($this->details['year'] == 2018) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/18-19/1';
        } else if ($this->details['year'] == 2019) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/19-20/1';
        } else if ($this->details['year'] == 2020) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/20-21/1';
        } else if ($this->details['year'] == 2021) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/21-22/1';
        } else if ($this->details['year'] == 2022) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/22-23/1';
        } else if ($this->details['year'] == 2023) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/23-24/1';
        } else if ($this->details['year'] == 2024) {
            $namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/24-25/1';
        } else {
            exit_with_error('Namespace is unknown for year ' . $this->details['year']);
        }

        $period_range = substr($this->details['year'], -2);
        $period_range = $period_range . '-' . ($period_range + 1);

        $xml = '
                <IRenvelope xmlns="' . xml($namespace) . '">' . $this->HMRC_RTI_Repository->request_header_get_xml() . '
                    <EmployerPaymentSummary>
                        <EmpRefs>
                            <OfficeNo>' . xml($this->details['tax_office_number']) . '</OfficeNo>
                            <PayeRef>' . xml($this->details['tax_office_reference']) . '</PayeRef>
                            <AORef>' . xml($this->details['accounts_office_reference']) . '</AORef>';

        if ($this->details['corporation_tax_reference'] != '' && $this->details['year'] >= 2014) {
            $xml .= '
                            <COTAXRef>' . xml($this->details['corporation_tax_reference']) . '</COTAXRef>';
        }

        $xml .= '
                        </EmpRefs>';

        if (false) {

            $xml .= '
                        <NoPaymentForPeriod>yes</NoPaymentForPeriod>'; // No payment due, as no employees paid in this pay period.

        }else if ($this->allowance_indicator!=null &&  ($this->allowance_indicator==true || $this->allowance_indicator==false)) {

            $xml .= '
                        <EmploymentAllowanceIndicator>'. xml($this->allowance_indicator ? 'true':'false') .'</EmploymentAllowanceIndicator>'; // Means that the employer is claiming/not claiming Employment Allowance (EA) for the current tax year.
        } else {

            $xml .= '
                        <RecoverableAmountsYTD>
                            <SMPRecovered>'          . xml($this->eps_data['SMP_Recovered']) . '</SMPRecovered>
                            <SPPRecovered>'          . xml($this->eps_data['SPP_Recovered']) . '</SPPRecovered>
                            <SAPRecovered>'          . xml($this->eps_data['SAP_Recovered']) . '</SAPRecovered>
                            <ShPPRecovered>'         . xml($this->eps_data['ShPP_Recovered']) . '</ShPPRecovered>
                            <NICCompensationOnSMP>'  . xml($this->eps_data['NIC_CompensationOnSMP']) . '</NICCompensationOnSMP>
                            <NICCompensationOnSPP>'  . xml($this->eps_data['NIC_CompensationOnSPP']) . '</NICCompensationOnSPP>
                            <NICCompensationOnSAP>'  . xml($this->eps_data['NIC_CompensationOnSAP']) . '</NICCompensationOnSAP>
                            <NICCompensationOnShPP>' . xml($this->eps_data['NIC_CompensationOnShPP']) . '</NICCompensationOnShPP>
                            <CISDeductionsSuffered>' . xml($this->eps_data['CIS_DeductionsSuffered']) . '</CISDeductionsSuffered>
                        </RecoverableAmountsYTD>';

        }

        // <ApprenticeshipLevy>
        // 	<LevyDueYTD>1250.00</LevyDueYTD>
        // 	<TaxMonth>3</TaxMonth>
        // 	<AnnualAllce>15000.00</AnnualAllce>
        // </ApprenticeshipLevy>


        // <CISDeductions>
        //     <CISDeductionIndicator>false</CISDeductionIndicator>
        // </CISDeductions>

        $xml .= '
                        <RelatedTaxYear>' . xml($period_range) . '</RelatedTaxYear>';

        if (is_array($this->details['final'])) {

            $xml .= '
                        <FinalSubmission>
                            <ForYear>yes</ForYear>
                        </FinalSubmission>';

            if ($this->details['year'] < 2016) {

                $xml .= '
                        <QuestionsAndDeclarations>
                            <FreeOfTaxPaymentsMadeToEmployee>'              . xml($this->eps_data['final']['free_of_tax_payments']         ? 'yes' : 'no') . '</FreeOfTaxPaymentsMadeToEmployee>
                            <ExpensesVouchersOrBenefitsFromOthers>'         . xml($this->eps_data['final']['expenses_and_benefits']        ? 'yes' : 'no') . '</ExpensesVouchersOrBenefitsFromOthers>
                            <PersonEmployedOutsideUKWorkedFor30DaysOrMore>' . xml($this->eps_data['final']['employees_out_of_uk']          ? 'yes' : 'no') . '</PersonEmployedOutsideUKWorkedFor30DaysOrMore>
                            <PayToSomeoneElse>'                             . xml($this->eps_data['final']['employees_pay_to_third_party'] ? 'yes' : 'no') . '</PayToSomeoneElse>
                            <P11DFormsDue>'                                 . xml($this->eps_data['final']['p11d_forms_due']               ? 'yes' : 'no') . '</P11DFormsDue>
                            <ServiceCompany>'                               . xml($this->eps_data['final']['service_company']              ? 'yes' : 'no') . '</ServiceCompany>
                        </QuestionsAndDeclarations>';

            }

        } else if ($this->details['final'] !== false) {

            exit_with_error('Invalid "final" value (should be false, or an array)');

        }

        $xml .= '
                    </EmployerPaymentSummary>
                </IRenvelope>';

        return $xml;

    }
}


// <NICsHoliday>'           . xml($this->eps_data['NIC_sHoliday']) . '</NICsHoliday>
// <SSPRecovered>'          . xml($this->eps_data['SSP_Recovered']) . '</SSPRecovered>
