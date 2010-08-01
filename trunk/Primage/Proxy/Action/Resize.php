<?php

class Primage_Proxy_Action_Resize extends Primage_Proxy_Action_Abstract {
	
	protected $width;
	protected $height;
	protected $holdRatio;

	public function __construct($width = null, $height = null, $holdRatio = true) {
		$this->width = $width;
		$this->height = $height;
		$this->holdRatio = $holdRatio;
	}

	public function make(Primage $image) {
		$image->resize($this->width, $this->height, $this->holdRatio);
	}
}