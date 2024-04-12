<?php

namespace carono\yii2parser;

use Exception;
use Psr\Http\Client\ClientInterface;
use yii\base\Model;
use yii\helpers\FileHelper;

abstract class SiteParser extends Model
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
//        $response = $this->getClient()->get($uri, $options);
//        if ($this->storePHPSESSID) {
//            foreach ($response->getHeader('Set-Cookie') as $c) {
//                $cookie = SetCookie::fromString($c);
//                if ($cookie->getName() === 'PHPSESSID') {
//                    $cookie->setDomain(parse_url($uri, PHP_URL_HOST));
//                    $cookie->setExpires(strtotime('+7 day'));
//                    $this->getCookieJar()->setCookie($cookie);
//                    break;
//                }
//            }
//        }
        return $response;
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
//        try {
//            return $this->_cookieJar ?: $this->_cookieJar = new FileCookieJar($this->getCookieFile());
//        } catch (\Exception $e) {
//            \Yii::error($e);
//            $this->clearCookies();
//            return $this->getCookieJar();
//        }
    }

    /**
     * @return Client
     */
    public function getClient()
    {
//        $cookie = $this->getCookieJar();
//        $options = array_merge(['cookies' => $cookie], $this->getClientOptions());
//        return $this->_client ?: $this->_client = new Client($options);
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