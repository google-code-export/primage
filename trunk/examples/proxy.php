<?php

// Autoload classes
define('PRIMAGE_BASE_DIR', '..');
define('ORIGINAL_IMAGES_DIR', 'original_images');
define('PUBLIC_IMAGES_DIR', 'images');

/**************************************************************
	AUTOLOAD
 **************************************************************/

function autoloadByDir($class) {
	$filePath = PRIMAGE_BASE_DIR . '/' . str_replace('_', '/', $class) . '.php';
	if(is_file($filePath)) {
		return require_once ($filePath);
	}
	$filePath = PRIMAGE_BASE_DIR . '/' . $class . '/' . str_replace('_', '/', $class) . '.php';
	if(is_file($filePath)) {
		return require_once ($filePath);
	}
}
spl_autoload_register('autoloadByDir');

/**************************************************************
	PROXY ROUTER CONFIGURATION
 **************************************************************/

$router = new Primage_Proxy_Router(false);

/**************************************************************
	AVATARS IMAGES
 **************************************************************/

$avatarsBaseUri = 'avatars/{id}_';
$avatarsSrcType = 'jpg';
$avatarsDstType = 'jpg';
$avatarsStorage = new Primage_Proxy_Storage(ORIGINAL_IMAGES_DIR . '/avatars', $avatarsSrcType, 90);
$avatarsProxyStorage = new Primage_Proxy_Storage(PUBLIC_IMAGES_DIR . '/avatars', $avatarsSrcType, 80);

$avatarsBig = new Primage_Proxy_Controller_CopyById($avatarsStorage, $avatarsProxyStorage);
$avatarsBig->addAction(new Primage_Proxy_Action_Resize(200, 300));
$router->addController($avatarsBaseUri . 'big.' . $avatarsDstType, $avatarsBig);

$avatarsMedium = new Primage_Proxy_Controller_CopyById($avatarsStorage, $avatarsProxyStorage);
$avatarsMedium->addAction(new Primage_Proxy_Action_Resize(50, 50));
$router->addController($avatarsBaseUri . 'medium.' . $avatarsDstType, $avatarsMedium);

$avatarsSmall = new Primage_Proxy_Controller_CopyById($avatarsStorage, $avatarsProxyStorage);
$avatarsSmall->addAction(new Primage_Proxy_Action_Resize(25, 25));
$router->addController($avatarsBaseUri . 'small.' . $avatarsDstType, $avatarsSmall);

/**************************************************************
	CLIPART IMAGES
 **************************************************************/

$clipartBaseUri = 'clipart/{id}_';
$clipartSrcType = 'jpg';
$clipartDstType = 'jpg';
$clipartStorage = new Primage_Proxy_Storage(ORIGINAL_IMAGES_DIR . '/clipart', $clipartSrcType, 90);
$clipartProxyStorage = new Primage_Proxy_Storage(PUBLIC_IMAGES_DIR . '/clipart', $clipartDstType, 80);
$watermarkAction = new PRimage_Proxy_Action_Watermark(ORIGINAL_IMAGES_DIR . '/watermark.png', -10, -10, 50);

$clipartBig = new Primage_Proxy_Controller_CopyById($clipartStorage, $clipartProxyStorage);
$clipartBig->addAction(new Primage_Proxy_Action_Resize(400, 400));
$clipartBig->addAction($watermarkAction);
$router->addController($clipartBaseUri . 'big.' . $clipartDstType, $clipartBig);

$clipartMedium = new Primage_Proxy_Controller_CopyById($clipartStorage, $clipartProxyStorage);
$clipartMedium->addAction(new Primage_Proxy_Action_Resize(200, 200));
$clipartMedium->addAction($watermarkAction);
$router->addController($clipartBaseUri . 'medium.' . $clipartDstType, $clipartMedium);

$clipartThumb = new Primage_Proxy_Controller_CopyById($clipartStorage, $clipartProxyStorage);
$clipartThumb->addAction(new Primage_Proxy_Action_Resize(100, 100));
$router->addController($clipartBaseUri . 'small.' . $clipartDstType, $clipartThumb);

/**************************************************************
	PROCESS REQUESTED IMAGE
 **************************************************************/

$controller = $router->getController($_SERVER['REQUEST_URI'], &$params);

if($controller) {
	$sendResultToStdout = false;
	try {
		$controller->dispatch($params, $sendResultToStdout);
	}
	catch(Primage_Proxy_Storage_SourceNotFound $e) {
		header("HTTP/1.0 404 Not Found");
		exit(0);
	}
	
	if(!$sendResultToStdout) {
		header('Location: ' . $_SERVER['REQUEST_URI']);
		exit(0);
	}
}
else {
	header("HTTP/1.0 404 Not Found");
	exit(0);
}