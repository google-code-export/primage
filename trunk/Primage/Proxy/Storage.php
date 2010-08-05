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
	
	public function __get($var) {
		return $this->$var;
	}

	public function storeImage(Primage $image, $id = null) {
		if(!$id) {
			$id = $this->getRandomId();
		}
		$id = $this->clearId($id);
		if($this->storeHandler) {
			$this->storeHandler->makeActionsOnImage($image);
		}
		$this->saveImageToStorage($image, $id);
		return $id;
	}

	protected function saveImageToStorage(Primage $image, $id) {
		$filepath = $this->dir . DIRECTORY_SEPARATOR . $id . '.' . $this->imageType;
		$image->saveToFile($filepath, $this->imageQuality);
	}

	public function isImage($id) {
		return is_file($this->getImageFilepath($id));
	}

	
	/**
	 * @param string $id
	 * @return Primage
	 */
	public function getImage($id) {
		$filepath = $this->getImageFilepath($id);
		if(!$this->isImage($id)) {
			throw new Primage_Proxy_Storage_SourceNotFound($filepath);
		}
		return Primage::buildFromFile($filepath);
	}

	protected function clearId($id) {
		return preg_replace(array('/\..{3,4}$/', '![/\\\\]!'), array('', ''), $id);
	}

	public function clearStorage($regexpFilter = null) {
		foreach(new DirectoryIterator($this->dir) as $fsObject) {
			if($fsObject->isFile()) {
				if(!$regexpFilter || preg_match($regexpFilter, $fsObject->getPathname())) {
					unlink($fsObject->getPathname());
				}
			}
		}
	}

	protected function getImageFilepath($id) {
		return $this->dir . DIRECTORY_SEPARATOR . $id . '.' . $this->imageType;
	}

	/***************************************************************
  STORE FROM FILE/URL
	 **************************************************************/
	
	public function loadImageFromFile($filepath) {
		if(!is_file($filepath)) {
			throw new Primage_Proxy_Storage_SourceNotFound($filepath);
		}
		try {
			$image = Primage::buildFromFile($filepath);
		}
		catch(Exception $e) {
			throw new Primage_Proxy_Storage_WrongImageFormat('Opening file "' . $filepath . '" failed. Original exception: ' . print_r($e, 1));
		}
		return $image;
	}

	public function loadImageFromUpload($_FILE) {
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

	public function loadImageFromUrl($url) {
		$tmpFilepath = $this->getRandomTmpFilepath();
		$this->downloadFileFromUrl($url, $tmpFilepath);
		
		try {
			$image = $this->loadImageFromFile($tmpFilepath);
		}
		catch(Exception $e) {
			if(is_file($tmpFilepath)) {
				unlink($tmpFilepath);
			}
			throw new $e();
		}
		if(is_file($tmpFilepath)) {
			unlink($tmpFilepath);
		}
		
		return $image;
	}

	protected function downloadFileFromUrl($url, $dstFilepath) {
		$fr = @fopen($url, 'r');
		if($fr === false) {
			throw new Primage_Proxy_Storage_SourceNotFound($url);
		}
		$fw = fopen($dstFilepath, 'w');
		if($fw === false) {
			throw new Exception('Writing to file "' . $dstFilepath . '" failed');
		}
		
		$timeLimit = 1000;
		set_time_limit($timeLimit);
		$deadline = time() + 1000;
		
		while(!feof($fr)) {
			$bufferString = fread($fr, 10000);
			fwrite($fw, $bufferString);
			if($deadline - time() < 10) {
				fclose($fw);
				fclose($fr);
				unlink($dstFilepath);
				throw new Primage_Proxy_Storage_SourceNotFound($url);
			}
		}
		fclose($fw);
		fclose($fr);
	}

	protected function getRandomTmpFilepath() {
		return $this->dir . '/_tmp_' . $this->getRandomId();
	}

	protected function getRandomId() {
		return md5(mt_rand() . mt_rand() . mt_rand() . mt_rand());
	}
}

class Primage_Proxy_Storage_Exception extends Exception {
}

class Primage_Proxy_Storage_SourceNotFound extends Primage_Proxy_Storage_Exception {
}

class Primage_Proxy_Storage_WrongImageFormat extends Primage_Proxy_Storage_Exception {
}
