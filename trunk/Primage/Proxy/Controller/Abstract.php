<?php

abstract class Primage_Proxy_Controller_Abstract extends Primage_Proxy_Handler {

	/**
	 * @param array $params
	 * @return Primage
	 */
	abstract public function dispatch(array $params, $sendResultToStdout = true);

}