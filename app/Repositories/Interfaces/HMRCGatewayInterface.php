<?php

namespace App\Repositories\Interfaces;

interface HMRCGatewayInterface {

    public function log_table_set($db,$table);
    public function submission_url_get();
    public function poll_url_get();
    public function vendor_set($vendor_code, $vendor_name);
    public function sender_set($sender_name, $sender_pass, $sender_email);
    public function request_submit($request);
    public function request_list($message_class);
    public function request_poll($request, $return_error);
    public function request_delete($request);
    public function response_debug_get();
    public function _send($message);
}