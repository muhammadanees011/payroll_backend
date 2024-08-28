<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Connection
{
    protected $timeout;
    protected $exitOnError;
    protected $headers;
    protected $response;

    public function __construct()
    {
        $this->timeout = 15; // Default timeout
        $this->exitOnError = true; // Default behavior to exit on error
        $this->headers = [];
        $this->response = null;
    }

    /**
     * Set the timeout for the HTTP request.
     *
     * @param int $seconds
     * @return $this
     */
    public function timeout_set($seconds)
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Set whether the script should exit on error.
     *
     * @param bool $exit
     * @return $this
     */
    public function exit_on_error_set($exit)
    {
        $this->exitOnError = $exit;
        return $this;
    }

    /**
     * Set headers for the HTTP request.
     *
     * @param string $header
     * @param string $value
     * @return $this
     */
    public function header_set($header, $value)
    {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * Send an HTTP POST request.
     *
     * @param string $url
     * @param string $data
     * @return $this
     */
    public function post($url, $data)
    {
        try {
            $this->response = Http::withHeaders($this->headers)
                ->timeout($this->timeout)
                ->post($url, ['body' => $data]);

            if ($this->response->failed() && $this->exitOnError) {
                $this->exit_with_error('HTTP request failed.', $this->response->body());
            }

        } catch (\Exception $e) {
            if ($this->exitOnError) {
                $this->exit_with_error('Exception during HTTP request.', $e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Get the response code from the last HTTP request.
     *
     * @return int|null
     */
    public function response_code_get()
    {
        return $this->response ? $this->response->status() : null;
    }

    /**
     * Get the full response from the last HTTP request.
     *
     * @return string|null
     */
    public function response_full_get()
    {
        return $this->response ? $this->response->body() : null;
    }

    /**
     * Get the response data from the last HTTP request.
     *
     * @return string|null
     */
    public function response_data_get()
    {
        return $this->response ? $this->response->body() : null;
    }

    /**
     * Exit the script with an error message.
     *
     * @param string $message
     * @param string $details
     */
    protected function exit_with_error($message, $details)
    {
        // Handle the error according to your application needs.
        // This can be customized to throw exceptions or log errors.
        exit($message . "\n" . $details);
    }
}
