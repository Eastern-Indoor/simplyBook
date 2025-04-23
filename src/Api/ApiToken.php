<?php

namespace Jkdow\SimplyBook\Api;

use DateTime;
use Exception;
use Jkdow\SimplyBook\Support\Logger;

class ApiToken
{
    protected $storageDir = null;
    protected $token = null;

    public function __construct($storageDir)
    {
        $this->storageDir = $storageDir;
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
        $files = glob($this->storageDir . '/.token-*');
        return match (count($files)) {
            1 => $this->checkTokenFile($files),
            default => $this->getNewToken($files),
        };
    }

    /**
     * Deletes the token files.
     *
     * @param array $files
     */
    protected function deleteTokens($files)
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Creates a new token file.
     *
     * @param array $files
     * @return string
     * @throws Exception
     */
    protected function getNewToken($files = [])
    {
        // Clear the old token files
        $this->deleteTokens($files);
        Logger::info("Getting new token");
        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        try {
            $token = $loginClient->getUserToken(
                smbk_config('api.company'),
                smbk_config('api.login'),
                smbk_config('api.password')
            );
        } catch (Exception $e) {
            Logger::error('Failed to get token', [$e->getMessage()]);
            throw new Exception('Failed to get token');
        }
        $token = $token[0];
        $datetime = (new DateTime())->format('Y-m-d_H-i-s');
        $filename = ".token-{$datetime}";
        $filepath = "{$this->storageDir}/{$filename}";
        file_put_contents($filepath, $token);
        return $token;
    }

    protected function checkTokenFile($files)
    {
        $file = $files[0];
        $filename = basename($file);
        $timestamp = str_replace('.token-', '', $filename);
        $timestamp = DateTime::createFromFormat('Y-m-d_H-i-s', $timestamp);
        Logger::info("Token file timestamp", [$timestamp]);
        $datetime = new DateTime();
        $datetime->modify('-1 hour');
        if ($timestamp > $datetime) {
            Logger::info("Using existing token");
            return file_get_contents($file);
        } else {
            Logger::info("Token expired, getting new token");
            return $this->getNewToken($files);
        }
    }
}
