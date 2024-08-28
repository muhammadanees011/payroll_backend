<?php

function xml($string) {
    return htmlspecialchars($string, ENT_XML1, 'UTF-8');
}

function exit_with_error($message,$erro_data){
    print_r($erro_data);
    exit($message);
}