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
		$ret = $this->doPost('user/login', [
		   'username' => $username,
		   'password' => $password
		]);
		
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
		$ret = $this->doPost('people/'.$uid.'/add_to_group', ['gid' => $gid]);
		
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

	private function doPost($path, $params)
	{
		$opts = array(
		   'http' => array(
		      'method'  => 'POST',
		      'header'  => ['Content-type: application/x-www-form-urlencoded', 'X-SessionId: '.$this->getSessionId()],
		      'content' => http_build_query($params),
		   ),
		);
		
		$context  = stream_context_create($opts);
		$result = file_get_contents('https://'.$this->subDomain.'.'.$this->domain.$this->apiBase.$path, FALSE, $context);
		
		return json_decode($result);
	}
}
	
?>
