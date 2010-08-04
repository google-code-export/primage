<?php

abstract class Primage_Proxy_Controller_Abstract extends Primage_Proxy_Handler {

	abstract public function dispatch($params = array(), $showImage = false);

}