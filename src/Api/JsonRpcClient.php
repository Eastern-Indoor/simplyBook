<?php

namespace Jkdow\SimplyBook\Api;

use Jkdow\SimplyBook\Support\Logger;

/**
 * JSON-RPC CLient class
 */
class JsonRpcClient
{

    protected $requestId = 1;
    protected $headers;
    protected $url;

    /**
     * Constructor. Takes the connection parameters
     *
     * @param String $url
     */
    public function __construct($url, $options = array())
    {
        $this->url = $url;

        // WP likes headers as an associative array
        $default = ['Content-Type' => 'application/json; charset=utf-8'];
        $this->headers = isset($options['headers'])
            ? array_merge($default, $options['headers'])
            : $default;
    }

    /**
     * Performs a jsonRCP request and return result
     *
     * @param String $method
     * @param Array $params
     * @return Collection
     */
    public function __call($method, $params)
    {
        //Logger::debug('JSON-RPC call', [$method, $params]);
        $currentId = $this->requestId++;
        $request = array(
            'method' => $method,
            'params' => array_values($params),
            'id' => $currentId,
            "jsonrpc" => "2.0"
        );
        $request = wp_json_encode($request);
        //Logger::debug('JSON-RPC request', [$request]);

        $response = wp_remote_post($this->url, [
            'headers'     => $this->headers,
            'body'        => $request,
            'timeout'     => 15,
            'data_format' => 'body',
        ]);
        if (is_wp_error($response)) {
            Logger::error("HTTP request failed: " . $response->get_error_message());
            return null;
        }
        $code = wp_remote_retrieve_response_code($response);
        if (200 !== (int) $code) {
            Logger::error("Unexpected HTTP code: {$code}\nURL: {$this->url}\nPayload: {$request}");
            return null;
        }
        $raw = wp_remote_retrieve_body($response);
        $result = json_decode($raw, true);
        //Logger::debug("Response", [$result]);
        if (null === $result) {
            Logger::error("Invalid JSON response:\n{$raw}");
            return null;
        } else if (! isset($result['id']) || (int)$result['id'] !== $currentId) {
            Logger::error("Mismatched response ID (req: {$currentId}, resp: " . ($result['id'] ?? 'none') . ")\n{$raw}");
            return null;
        } else if (! empty($result['error'])) {
            $msg = $result['error']['message'] ?? 'Unknown JSON-RPC error';
            Logger::error("JSON-RPC error: {$msg}\n{$raw}");
            return null;
        }

        return collect($result['result']);
    }
}
