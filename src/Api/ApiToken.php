<?php

namespace Jkdow\SimplyBook\Api;

use DateTime;
use Exception;
use Jkdow\SimplyBook\Support\Config;
use Jkdow\SimplyBook\Support\Logger;

class ApiToken
{
    protected $storageDir = null;
    protected $token = null;

    public function __construct()
    {
        $this->token = $this->getToken();
    }

    /**
     * Get the token.
     *
     * @return string
     */
    public function token()
    {
        return $this->token;
    }

    /**
     * Force refresh of the token.
     *
     * @return string
     */
    public function refreshToken()
    {
        $this->token = self::getNewToken();
        return $this->token;
    }

    /**
     * Internal method to read the token file or to create a new one.
     *
     * @return string
     */
    protected function getToken()
    {
        $token = smbk_config('token.token');
        $timestamp = smbk_config('token.time');
        if ($token == '') {
            return $this->getNewToken();
        } else {
            return $this->checkToken($token, $timestamp);
        }
    }

    /**
     * Creates a new token.
     *
     * @param array $files
     * @return string
     * @throws Exception
     */
    protected function getNewToken()
    {
        // Clear the old token files
        //Logger::info("Getting new token");
        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $company = smbk_config('api.company');
        $login = smbk_config('api.login');
        $password = smbk_config('api.password');
        if (empty($company) || empty($login) || empty($password)) {
            smbk_flash('Please set your API credentials in the settings', 'error');
            return;
        }
        try {
            $token = $loginClient->getUserToken($company, $login, $password);
        } catch (Exception $e) {
            smbk_flash('Error getting token: ' . $e->getMessage(), 'error');
            return;
        }
        $token = $token[0];
        $datetime = (new DateTime())->format('Y-m-d_H-i-s');
        Config::set('token.token', $token);
        Config::set('token.time', $datetime);
        return $token;
    }

    protected function checkToken($token, $timestamp)
    {
        if (empty($timestamp)) {
            return $this->getNewToken();
        }
        $timestamp = DateTime::createFromFormat('Y-m-d_H-i-s', $timestamp);
        Logger::info("Token file timestamp", [$timestamp]);
        $datetime = new DateTime();
        $datetime->modify('-1 hour');
        if ($timestamp > $datetime) {
            Logger::info("Using existing token");
            return $token;
        } else {
            Logger::info("Token expired, getting new token");
            return $this->getNewToken();
        }
    }
}
