<?php

class Primage_Proxy_Router {
	
	protected $controllers = array();
	
	protected $uriTemplates;
	protected $uriRegexp;
	protected $uriVars = array();
	protected $uriVarsTagged = array();
	
	const URI_TEMPLATE_VAR_LEFT_TAG = '{';
	const URI_TEMPLATE_VAR_RIGHT_TAG = '}';

	protected function initUriTemplate($uriTemplate) {
		$uriTemplate = $this->getRealUri($uriTemplate);
		$this->uriTemplates[] = $uriTemplate;
		if(preg_match_all('/' . preg_quote(self::URI_TEMPLATE_VAR_LEFT_TAG) . '(.*?)' . preg_quote(self::URI_TEMPLATE_VAR_RIGHT_TAG) . '/', $uriTemplate, $matches)) {
			$this->uriVars[] = $matches[1];
			$this->uriVarsTagged[] = $matches[0];
			$this->uriRegexp[] = '!' . str_replace($matches[0], '(.+?)', $uriTemplate) . '$!u';
		}
		else {
			throw new Exception('Router URI template must have some vars');
		}
	}

	protected function getRealUri($uri) {
		return str_replace('\\', '/', trim($uri, '/\\'));
	}

	public function getController($uri, &$params) {
		$uri = $this->getRealUri($uri);
		$uriIndex = $this->getMatchedUriIndex($uri);
		if($uriIndex !== false) {
			$params = $this->getVarsFromUri($uri, $uriIndex);
			return $this->controllers[$uriIndex];
		}
	
	}

	protected function getMatchedUriIndex($uri) {
		foreach($this->uriRegexp as $i => $regexp) {
			if(preg_match($regexp, $uri)) {
				return $i;
			}
		}
		return false;
	}

	protected function getVarsFromUri($uri, $index) {
		$vars = array();
		if(preg_match($this->uriRegexp[$index], $uri, $matches)) {
			foreach($this->uriVars[$index] as $i => $var) {
				$vars[$var] = $matches[$i + 1];
			}
		}
		return $vars;
	}

	public function addController($uriTemplate, Primage_Proxy_Controller_Abstract $controller) {
		$this->initUriTemplate($uriTemplate);
		$this->controllers[] = $controller;
	}
}