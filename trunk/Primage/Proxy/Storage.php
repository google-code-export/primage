<?php

// TODO: refactoring to interface is required
class Primage_Proxy_Storage {
	
	protected $dir;
	protected $imageQuality;
	protected $imageType;
	
	/**
	 * @var Primage_Proxy_Handler
	 */
	protected $storeHandler;

	public function __construct($dir, $imageType = null, $imageQuality = 80, Primage_Proxy_Handler $storeHandler = null) {
		if(!is_dir($dir)) {
			throw new Exception('Directory "' . $dir . '" not found');
		}
		$this->dir = realpath($dir);
		$this->imageType = $imageType;
		$this->imageQuality = $imageQuality;
		$this->storeHandler = $storeHandler;
	}

	public function storeImage(Primage $image, $uid) {
		$uid = $this->clearUid($uid);
		
		if($this->storeHandler) {
			$this->storeHandler->makeActionsOnImage($image);
		}
		
		$this->saveImageToStorage($image, $uid);
	}

	public function isImage($uid) {
		return is_file($this->getImageFilepath($uid));
	}

	public function getImage($uid) {
		return Primage::buildFromFile($this->getImageFilepath($uid));
	}

	protected function clearUid($uid) {
		return preg_replace(array('/\..{3,4}$/', '![/\\\\]!'), array('', ''), $uid);
	}

	// TODO: clear by date of file last access limit
	public function clearStorage($regexpFilter = null) {
		foreach(new DirectoryIterator($this->dir) as $fsObject) {
			if($fsObject->isFile()) {
				if(!$regexpFilter || preg_match($regexpFilter, $fsObject->getPathname())) {
					echo $fsObject->getPathname() . '<BR>';
					//					unlink($fsObject->getPathname());
				}
			}
		}
	}

	protected function saveImageToStorage(Primage $image, $uid) {
		$filepath = $this->dir . DIRECTORY_SEPARATOR . $uid . '.' . $this->imageType;
		$image->saveToFile($filepath, $this->imageQuality);
	}

	protected function getImageFilepath($uid) {
		return $this->dir . DIRECTORY_SEPARATOR . $uid . '.' . $this->imageType;
	}

	public function getImageFromStorage($uid) {
	
	}
}