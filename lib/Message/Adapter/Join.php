<?php

namespace Message\Adapter;

class Join extends AdapterAbstract
{
    protected $notify_url = "https://joinjoaomgcd.appspot.com/_ah/api/messaging/v1/sendPush";

    protected $deviceId = 'group.all';

    /**
     * @param string $message
     * @throws \Exception
     * @return array
     */
    public function send($message, array $options = array())
    {
        $this->setOptions($options);
        $message = trim($message);
        if (!$this->token || !$this->deviceId) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }

        $params = [
            "apikey"   => $this->getToken(),
            "deviceId" => $this->getDeviceId(),
            "title"    => $this->getTitle(),
            "text"     => $message,
            "icon"     => 'http://blog.shanegraphique.com/wp-content/uploads/2016/02/037.jpg'
        ];
        if ($url = $this->getUrl()) {
            $params["url"] = $url;
        }

        $this->setNotifyUrl(sprintf('%s?%s', $this->getNotifyUrl(), http_build_query($params)));
        $curl = $this->getCurl([
            CURLOPT_POST => false,
        ]);

        $response = curl_exec($curl);
        if ($response === false) {
            throw new \Exception("cURL Error: " . curl_error($curl));
        }

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 !== $code) {
            $response = json_decode($response, true);
            throw new \Exception("Une érreur est survenue.");
        }

        return true;
    }

    /**
     * @return string
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * @param string $deviceId
     * @return Join
     */
    public function setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;
        return $this;
    }
}