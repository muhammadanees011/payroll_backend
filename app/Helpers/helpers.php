<?php

function xml($string) {
    return htmlspecialchars($string, ENT_XML1, 'UTF-8');
}

function exit_with_error($message,$erro_data){
    print_r($erro_data);
    exit($message);
}

function validate_xml($xmlString){
    ////////////////////////////////
    // $xml = simplexml_load_string($xmlString);
    // if ($xml === false) {
    //     echo "Failed loading XML";
    //     foreach(libxml_get_errors() as $error) {
    //         echo "<br>", $error->message;
    //     }

    //     return libxml_get_errors();
    // } else {
    //     echo "XML is valid";
    // }

    ////////////////////////////////
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    if ($dom->loadXML($xmlString)) {
        echo "XML is valid";
    } else {
        echo "Invalid XML";
        foreach(libxml_get_errors() as $error) {
            echo "<br>", $error->message;
        }
    }
    libxml_clear_errors();
}


// Format Currency (GBP)
function formatCurrency($value)
{
    if ($value !== null) {
        return '£' . number_format($value, 2);
    }
    return null;
}


// Calculate Subtotal for PayItems
function calculateSubTotal($employeePaySlip, $itemIndex)
{
    $subTotal = $employeePaySlip['base_pay'] - (
        $employeePaySlip['employee_pension'] +
        $employeePaySlip['paye_income_tax'] +
        $employeePaySlip['employee_nic']
    );

    foreach ($employeePaySlip['payitems'] as $index => $item) {
        if ($itemIndex <= $index) {
            $subTotal += $item['amount'];
        }
    }

    return formatCurrency($subTotal);
}
