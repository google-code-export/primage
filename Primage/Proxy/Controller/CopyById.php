<?php

class Primage_Proxy_Controller_CopyById extends Primage_Proxy_Controller_Abstract {
	
	/**
	 * @var Primage_Proxy_Storage
	 */
	protected $srcStorage;
	
	/**
	 * @var Primage_Proxy_Storage
	 */
	protected $dstStorage;

	public function __construct(Primage_Proxy_Storage $srcStorage, Primage_Proxy_Storage $dstStorage) {
		$this->srcStorage = $srcStorage;
		$this->dstStorage = $dstStorage;
	}

	protected function getImageByParams(array $params) {
		if(empty($params['id'])) {
			throw new Exception('Parameter "id" is required');
		}
		return $this->srcStorage->getImage($params['id']);
	}

	/**
	 * @param Primage $image
	 * @param array $params
	 */
	protected function postDispatch(Primage $image, array $params) {
		$this->dstStorage->storeImage($image, basename($_SERVER['REQUEST_URI']));
	}
}