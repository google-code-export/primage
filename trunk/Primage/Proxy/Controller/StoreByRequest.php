<?php

class Primage_Proxy_Controller_StoreByRequest extends Primage_Proxy_Controller_Abstract {
	
	protected $srcStorage;
	protected $dstStorage;
	protected $imageType;
	protected $imageQuality;

	public function __construct(Primage_Proxy_Storage $srcStorage, $imageType = 'jpg', $imageQuality = 80) {
		$this->srcStorage = $srcStorage;
		$this->dstStorage = new Primage_Proxy_Storage($this->getRequestDir(), $imageType, $imageQuality);
		$this->imageType = $imageType;
		$this->imageQuality = $imageQuality;
	}

	protected function getRequestDir() {
		return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . dirname(str_replace(array('\\', '/'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), trim($_SERVER['REQUEST_URI'], '/\\')));
	}

	public function dispatch($params = array(), $showImage=false) {
		if(empty($params['filename'])) {
			throw new Exception('Parameter "filename" is required');
		}
		if(!$this->srcStorage->isImage($params['filename'])) {
			throw new Exception('File not found');
		}
		$image = $this->srcStorage->getImage($params['filename']);
		$this->makeActionsOnImage($image);
		$this->dstStorage->storeImage($image, basename($_SERVER['REQUEST_URI']));
		
		if($showImage) {
			$image->sendToStdout($this->imageType, $this->imageQuality);
		}
		return $image;
	}
}