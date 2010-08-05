<?php

class Primage_Proxy_Controller_CopyById extends Primage_Proxy_Controller_Abstract {
	
	protected $srcStorage;
	protected $dstStorage;

	public function __construct(Primage_Proxy_Storage $srcStorage, Primage_Proxy_Storage $dstStorage) {
		$this->srcStorage = $srcStorage;
		$this->dstStorage = $dstStorage;
	}

	/**
	 * @param array $params
	 * @return Primage
	 */
	public function dispatch(array $params, $sendResultToStdout = true) {
		if(empty($params['id'])) {
			throw new Exception('Parameter "id" is required');
		}
		if(!$this->srcStorage->isImage($params['id'])) {
			throw new Exception('File not found');
		}
		$image = $this->srcStorage->getImage($params['id']);
		$this->makeActionsOnImage($image);
		$this->dstStorage->storeImage($image, basename($_SERVER['REQUEST_URI']));
		
		if($sendResultToStdout) {
			$image->sendToStdout($this->dstStorage->imageType, $this->dstStorage->imageQuality);
		}
		
		return $image;
	}
}