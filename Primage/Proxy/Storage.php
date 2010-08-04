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
		if(!is_dir($tmpDir)) {
			throw new Exception('Directory "' . $tmpDir . '" not found');
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

	/***************************************************************
  STORE FROM FILE/URL
	 **************************************************************/
	
	public function storeImageFromFile($filepath, $deleteOriginal = false) {
		return $this->loadImageFromFile($filepath);
	}

	public function storeImageFromUpload($_FILE) {
		foreach(array('name', 'tmp_name', 'size') as $param) {
			if(empty($_FILE[$param])) {
				throw new Exception('Wrong $_FILE format');
			}
		}
		if(!is_uploaded_file($_FILE['tmp_name'])) {
			throw new Primage_Proxy_Storage_SourceNotFound($_FILE['name']);
		}
		
		$tmpFilepath = $this->getRandomTmpFilepath();
		
		if(!move_uploaded_file($_FILE['tmp_name'], $tmpFilepath)) {
			throw new Primage_Proxy_Storage_SourceNotFound($_FILE['name']);
		}
		$image = $this->loadImageFromFile($tmpFilepath);
		unlink($tmpFilepath);
		return $image;
	}

	public function storeImageFromUrl($url) {
		$tmpFilepath = $this->getRandomTmpFilepath();
		$this->downloadFileFromUrl($url, $tmpFilepath);
		
		try {
			$image = $this->loadImageFromFile($tmpFilepath);
		}
		catch(Exception $e) {
			unlink($tmpFilepath);
			throw new $e();
		}
		unlink($tmpFilepath);
		
		return $image;
	}

	protected function loadImageFromFile($filepath) {
		if(!is_file($filepath)) {
			throw new Primage_Proxy_Storage_SourceNotFound($filepath);
		}
		try {
			Primage::buildFromFile($filepath);
		}
		catch(Exception $e) {
			throw new Primage_Proxy_Storage_WrongImageFormat('Opening file "' . $filepath . '" failed. Original exception: ' . print_r($e, 1));
		}
	}

	protected function downloadFileFromUrl($url, $dstFilepath) {
		$fr = @fopen($url, 'r');
		if($fr === false) {
			throw new Primage_Proxy_Storage_SourceNotFound($url);
		}
		$fw = fopen($dstFilepath, 'w');
		
		$timeLimit = 1000;
		set_time_limit($timeLimit);
		$deadline = time() + 1000;
		
		while(!feof($fr)) {
			$bufferString = fread($fr, 10000);
			fopen($fw, $bufferString);
			if($deadline - time() > 10) {
				fclose($fw);
				fclose($fr);
				throw new Primage_Proxy_Storage_SourceNotFound($url);
			}
		}
		fclose($fw);
		fclose($fr);
	}

	protected function getRandomTmpFilepath() {
		return $this->dir . '/_tmp_' . md5(mt_rand() . mt_rand() . mt_rand() . mt_rand());
	}
}

class Primage_Proxy_Storage_Exception extends Exception {
}

class Primage_Proxy_Storage_SourceNotFound extends Primage_Proxy_Storage_Exception {
}

class Primage_Proxy_Storage_WrongImageFormat extends Primage_Proxy_Storage_Exception {
}
