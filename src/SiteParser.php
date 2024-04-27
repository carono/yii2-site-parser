<?php

namespace carono\yii2parser;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Psr\Http\Client\ClientInterface;
use yii\helpers\FileHelper;

abstract class SiteParser
{
    /**
     * @var ClientInterface
     */
    private $_client;
    public $login;
    public $password;
    public $authorizedUrl;

    private $_cookieJar;

    protected $storePHPSESSID = true;
    public $cookieFolder = 'cookies';

    public function __construct()
    {
        $cookie = $this->getCookieJar();
        $options = array_merge(['cookies' => $cookie], $this->getClientOptions());
        $client = new Client($options);
        $this->setClient($client);
    }

    public function setClient(ClientInterface $client)
    {
        $this->_client = $client;
    }

    public function postAuth($uri, $options)
    {
        $this->loginIfNeed();
        return $this->post($uri, $options);
    }

    public function getAuth($uri, $options = [])
    {
        $this->loginIfNeed();
        return $this->get($uri, $options);
    }

    public function get($uri, $options = [])
    {
        return $this->getClient()->get($uri, $options);
    }

    public function post($uri, $options = [])
    {
       return $this->getClient()->post($uri, $options);
    }

    public function getClientOptions()
    {
        return [
        ];
    }

    public function getCookieFile()
    {
        $cookieFolder = $this->cookieFolder;
        if (!is_dir($cookieFolder)) {
            FileHelper::createDirectory($cookieFolder);
        }

        $key = [static::class, $this->login, $this->password];
        return $cookieFolder . DIRECTORY_SEPARATOR . md5(implode(':', $key)) . '.json';
    }

    public function clearCookies()
    {
        if (file_exists($this->getCookieFile())) {
            unlink($this->getCookieFile());
            $this->_client = null;
        }
    }

    public function getCookieJar()
    {
        try {
            return $this->_cookieJar ?: $this->_cookieJar = new FileCookieJar($this->getCookieFile(), true);
        } catch (\Exception $e) {
            \Yii::error($e);
            $this->clearCookies();
            return $this->getCookieJar();
        }
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->_client;
    }

    public function loginIfNeed()
    {
        if (!$this->checkAuthorization() && !$this->loginToSite()) {
            throw new Exception('Fail To Login');
        }
    }

    /**
     * @return bool
     */
    abstract function loginToSite();

    /**
     * @return bool
     */
    public function checkAuthorization()
    {
        if (!$this->authorizedUrl) {
            throw new Exception('Authorized url is not set');
        }
        return $this->get($this->authorizedUrl, ['allow_redirects' => false])->getStatusCode() === 200;
    }
}