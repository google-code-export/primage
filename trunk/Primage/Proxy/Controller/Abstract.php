<?php

abstract class Primage_Proxy_Controller_Abstract {
	
	protected $actions = array();

	abstract public function dispatch($params = array());

	public function addAction(Primage_Proxy_Action_Abstract $action) {
		$this->actions[] = $action;
	}

	protected function makeActionsOnImage(Primage $image) {
		foreach($this->actions as $action) {
			$action->make($image);
		}
	}
}