<?php

class SimpleChurchAPI
{
    private $domain = 'simplechurchcrm.com';
    private $basePath = '/api/';

    private $sessionId = '';
    private $subDomain = '';

    public function __construct(array $args)
    {
        if (empty($args['subDomain'])) {
            throw new Exception("Argument \"subDomain\" is required.");
        }

        $this->setSubDomain($args['subDomain']);

        if (!empty($args['sessionId'])) {
            $this->setSessionId($args['sessionId']);
        }
    }

    public function setSubDomain($subDomain)
    {
    	$this->subDomain = $subDomain;

    	return $this;
    }

    public function getSubDomain()
    {
    	return $this->subDomain;
    }

    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function login($username, $password)
    {
        $ret = $this->doPost('user/login', array(
	        'username' => $username,
	        'password' => $password
        ));

        $this->setSessionId($ret->session_id);

        return $ret;
    }

    public function createPerson($params)
    {
        return $this->doPost('people', $params);
    }

    public function addPersonToGroup($uid, $gid)
    {
        return $this->doPost('people/'.$uid.'/add_to_group', array('gid' => $gid));
    }

    public function assignInteraction($params)
    {
        $params['op'] = 'assign';

        return $this->createInteraction($params);
    }

    public function logInteraction($params)
    {
        $params['op'] = 'log';

        return $this->createInteraction($params);
    }

    private function createInteraction($params)
    {
        return $this->doPost('interactions', $params);
    }

    public function getCalendarEvents($params)
    {
        return $this->doGet('calendar/events', $params);
    }

    public function getCalendarViews()
    {
        return $this->doGet('calendar/views');
    }

    private function buildRequestUrl($path, $params = array())
    {
        $url  = "https://{$this->getSubDomain()}.{$this->domain}";
        $url .= "{$this->basePath}{$path}";

        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    private function doGet($path, $params = array())
    {
        $headers = array('Content-type: application/json');

        return $this->doRequest('GET', $this->buildRequestUrl($path, $params), $headers);
    }

    private function doPost($path, $params)
    {
        $headers = array('Content-type: application/x-www-form-urlencoded');

        return $this->doRequest('POST', $this->buildRequestUrl($path), $headers, $params);
    }

    private function doRequest($method, $url, $headers = array(), $params = array())
    {
        $headers[] = 'X-SessionId: '.$this->getSessionId();

        $request = curl_init();

        curl_setopt($request, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

        if ($params) {
            curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $response = curl_exec($request);
        $response = json_decode($response);

        $statusCode = curl_getinfo($request, CURLINFO_HTTP_CODE);

        curl_close($request);

        $this->throwExceptionIfError($response, $statusCode);

        return $response->data;
    }

    private function throwExceptionIfError($response, $statusCode = null)
    {
        if ($response && $response->success) {
            return false;
        }

        if ($response) {
            throw new Exception($response->error, $response->statusCode);
        } else {
            throw new Exception('No response', $statusCode);
        }
    }
}
