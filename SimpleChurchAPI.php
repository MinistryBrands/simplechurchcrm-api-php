<?php

class SimpleChurchAPI
{
	var $sessionId = '';
	var $subDomain = '';
	var $domain = 'simplechurchcrm.com';
	var $apiBase = '/api/';
	
	public function __construct($opts = array())
	{
		if ($opts['sessionId'])
		{
			$this->setSessionId($opts['sessionId']);
		}
		
		if (!$opts['subDomain'])
		{
			throw new Exception('subDomain is required.');
		}
		
		$this->subDomain = $opts['subDomain'];
	}
	
	public function login($username, $password)
	{
		$ret = $this->doPost('user/login', array(
		   'username' => $username,
		   'password' => $password
		));
		
		if (!$ret->success)
		{
			throw new Exception('Invalid Username or Password');
		}
		else
		{
			$this->setSessionId($ret->data->session_id);
		}
		
		return $ret->data;
	}
	
	public function createPerson($params)
	{
		$ret = $this->doPost('people', $params);
		
		if (!$ret->success)
		{
			throw new Exception($ret->error);
		}
		
		return $ret->data;
	}
	
	public function addPersonToGroup($uid, $gid)
	{
		$ret = $this->doPost('people/'.$uid.'/add_to_group', array('gid' => $gid));
		
		if (!$ret->success)
		{
			throw new Exception($ret->error);
		}
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
		$ret = $this->doPost('interactions', $params);
		
		if (!$ret->success)
		{
			throw new Exception($ret->error);
		}
		
		return $ret->data;
	}
	
	public function getSessionId()
	{
		return $this->sessionId;
	}
	
	public function setSessionId($sessionId)
	{
		$this->sessionId = $sessionId;
		
		return $this;
	}

	public function getCalendarEvents($params)
	{
		$ret = $this->doGet('calendar/events', $params);

		if (!$ret->success)
		{
			throw new Exception($ret->error);
		}

		return $ret->data;
	}

	public function getCalendarViews()
	{
		$ret = $this->doGet('calendar/views');

		if (!$ret->success)
		{
			throw new Exception($ret->error);
		}

		return $ret->data;
	}

	private function buildRequestUrl($path, $params = array())
	{
		$url = 'https://'.$this->subDomain.'.'.$this->domain.$this->apiBase.$path;

		if ($params)
		{
			$url .= '?'.http_build_query($params);
		}

		return $url;
	}

	private function doGet($path, $params = array())
	{
		return $this->doRequest($this->buildRequestUrl($path, $params), array(
			'method' => 'GET',
			'header' => array('Content-type: application/json')
		));
	}

	private function doPost($path, $params)
	{
		return $this->doRequest($this->buildRequestUrl($path), array(
			'method'  => 'POST',
			'header'  => array('Content-type: application/x-www-form-urlencoded'),
			'content' => http_build_query($params)
		));
	}

	private function doRequest($url, $opts)
	{
		$opts['header'][] = 'X-SessionId: '.$this->getSessionId();

		$context = stream_context_create(array('http' => $opts));
		$result = file_get_contents($url, FALSE, $context);

		return json_decode($result);
	}
}
	
?>
