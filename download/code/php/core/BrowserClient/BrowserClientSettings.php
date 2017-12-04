<?php

namespace DownloadCore\BrowserClient;

use GuzzleHttp\Client as GuzzleClient;


class BrowserClientSettings extends GuzzleClient
{
    // http://php.net/manual/en/function.curl-setopt.php
    const CURL_SETTINGS = array(
        CURLOPT_TIMEOUT => 120,         // 900 = 15 min
        CURLOPT_CONNECTTIMEOUT => 120,  // 900 = 15 min
    );

//    protected $randomProxyIp;
//    protected $randomProxyPort;
//
//    protected $successfulProxyIp;
//    protected $successfulProxyPort;

    public function __construct(array $proxiesList)
    {
        parent::__construct(array(
            'curl' => $this->getSettingsArrayForClient($proxiesList),
        ));
    }

    /**
     * Get settings for the client (CURL)
     * @return array
     */
    protected function getSettingsArrayForClient($proxiesList)
    {
        $settings = self::CURL_SETTINGS;

        // If Proxy is on for the project
        if (isset($proxiesList) && !empty($proxiesList)) {
            $proxySettingsApplied = $proxiesList[array_rand($proxiesList)];

            $settings[CURLOPT_PROXY] =
                //$this->randomProxyIp =
                $proxySettingsApplied['ip'];
            $settings[CURLOPT_PROXYPORT] =
                //$this->randomProxyPort =
                $proxySettingsApplied['port'];
        }

        return $settings;

    }

//    /**
//     * @return mixed
//     */
//    public function getSuccessfulProxyIp()
//    {
//        return $this->successfulProxyIp;
//    }
//
//    /**
//     * @param mixed $successfulProxyIp
//     */
//    public function setSuccessfulProxyIp($successfulProxyIp)
//    {
//        $this->successfulProxyIp = $successfulProxyIp;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getSuccessfulProxyPort()
//    {
//        return $this->successfulProxyPort;
//    }
//
//    /**
//     * @param mixed $successfulProxyPort
//     */
//    public function setSuccessfulProxyPort($successfulProxyPort)
//    {
//        $this->successfulProxyPort = $successfulProxyPort;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getRandomProxyIp()
//    {
//        return $this->randomProxyIp;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getRandomProxyPort()
//    {
//        return $this->randomProxyPort;
//    }

    //const BAD_CONNECTION_EXCEPTION_HANDLER_BY_CLIENT = \GuzzleHttp\Exception\ConnectException::class;
    /**
     * Error handler handling bad connection (for example: proxy server is down and no connection possible)
     * @return string
     */
//    public function getBadConnectionExceptionHandlerClassName()
//    {
//        return self::BAD_CONNECTION_EXCEPTION_HANDLER_BY_CLIENT;
//    }

}