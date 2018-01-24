<?php
namespace SCCRM;

class SimpleChurchApi
{
    /**
     * @var string
     */
    private $basePath = '/api/';

    /**
     * @var string
     */
    private $domain = 'simplechurchcrm.com';

    /**
     * @var string
     */
    private $sessionId = '';

    /**
     * @var string
     */
    private $subDomain = '';

    /**
     * SimpleChurchApi constructor.
     * @param array $args
     * @throws \Exception
     */
    public function __construct(array $args)
    {
        if (empty($args['subDomain'])) {
            throw new \Exception('Argument "subDomain" is required.');
        }

        $this->setSubDomain($args['subDomain']);

        if (!empty($args['sessionId'])) {
            $this->setSessionId($args['sessionId']);
        }
    }

    /**
     * @param $subDomain
     * @return $this
     */
    public function setSubDomain($subDomain)
    {
        $this->subDomain = $subDomain;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubDomain()
    {
        return $this->subDomain;
    }

    /**
     * @param $sessionId
     * @return $this
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param $username
     * @param $password
     * @return mixed
     * @throws \Exception
     */
    public function login($username, $password)
    {
        $ret = $this->doPost('user/login', [
            'username' => $username,
            'password' => $password,
        ]);

        $this->setSessionId($ret->session_id);

        return $ret;
    }

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function createPerson($params)
    {
        return $this->doPost('people', $params);
    }

    /**
     * @param $uid
     * @param $gid
     * @return mixed
     * @throws \Exception
     */
    public function addPersonToGroup($uid, $gid)
    {
        return $this->doPost('people/'.$uid.'/add_to_group', ['gid' => $gid]);
    }

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function assignInteraction($params)
    {
        $params['op'] = 'assign';

        return $this->createInteraction($params);
    }

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function logInteraction($params)
    {
        $params['op'] = 'log';

        return $this->createInteraction($params);
    }

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    private function createInteraction($params)
    {
        return $this->doPost('interactions', $params);
    }

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function getCalendarEvents($params)
    {
        return $this->doGet('calendar/events', $params);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCalendarViews()
    {
        return $this->doGet('calendar/views');
    }

    /**
     * @param $path
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function get($path, $params = [])
    {
        return $this->doGet(trim($path, '/'), $params);
    }

    /**
     * @param $path
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function post($path, $params = [])
    {
        return $this->doPost(trim($path, '/'), $params);
    }

    /**
     * @param $path
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    private function doGet($path, $params = [])
    {
        $headers = ['Content-type: application/json'];

        return $this->doRequest('GET', $this->buildRequestUrl($path, $params), $headers);
    }

    /**
     * @param $path
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    private function doPost($path, $params)
    {
        $headers = ['Content-type: application/x-www-form-urlencoded'];

        return $this->doRequest('POST', $this->buildRequestUrl($path), $headers, $params);
    }

    /**
     * @param $method
     * @param $url
     * @param array $headers
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    private function doRequest($method, $url, $headers = [], $params = [])
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

    /**
     * @param $path
     * @param array $params
     * @return string
     */
    private function buildRequestUrl($path, $params = [])
    {
        $url  = "https://{$this->getSubDomain()}.{$this->domain}";
        $url .= "{$this->basePath}{$path}";

        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * @param $response
     * @param null $statusCode
     * @return bool
     * @throws \Exception
     */
    private function throwExceptionIfError($response, $statusCode = null)
    {
        if ($response && $response->success) {
            return false;
        }

        if ($response) {
            throw new \Exception($response->error, $response->statusCode);
        } else {
            throw new \Exception('No response', $statusCode);
        }
    }
}
