<?php

// Autoload classes
define('PRIMAGE_BASE_DIR', '..');
define('ORIGINAL_IMAGES_DIR', 'original_images');

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

$router = new Primage_Proxy_Router();

/**************************************************************
	AVATARS IMAGES
 **************************************************************/

$avatarsBaseUri = 'avatars/{filename}_';
$avatarsSrcType = 'jpg';
$avatarsDstType = 'gif';
$avatarsStorage = new Primage_Proxy_Storage(ORIGINAL_IMAGES_DIR . '/avatars', $avatarsSrcType, 95);

$avatarsBig = new Primage_Proxy_Controller_StoreByRequest($avatarsStorage, $avatarsDstType, 80);
$avatarsBig->addAction(new Primage_Proxy_Action_Resize(200, 300));
$router->addController($avatarsBaseUri . 'big.' . $avatarsDstType, $avatarsBig);

$avatarsMedium = new Primage_Proxy_Controller_StoreByRequest($avatarsStorage, $avatarsDstType, 80);
$avatarsMedium->addAction(new Primage_Proxy_Action_Resize(50, 50));
$router->addController($avatarsBaseUri . 'medium.' . $avatarsDstType, $avatarsMedium);

$avatarsSmall = new Primage_Proxy_Controller_StoreByRequest($avatarsStorage, $avatarsDstType, 80);
$avatarsSmall->addAction(new Primage_Proxy_Action_Resize(25, 25));
$router->addController($avatarsBaseUri . 'small.' . $avatarsDstType, $avatarsSmall);

/**************************************************************
	CLIPART IMAGES
 **************************************************************/

$clipartBaseUri = 'clipart/{filename}_';
$clipartSrcType = 'jpg';
$clipartDstType = 'jpg';
$clipartStorage = new Primage_Proxy_Storage(ORIGINAL_IMAGES_DIR . '/clipart', $clipartSrcType, 80);
$watermarkAction = new PRimage_Proxy_Action_Watermark(ORIGINAL_IMAGES_DIR . '/watermark.png', -10, -10, 50);

$clipartBig = new Primage_Proxy_Controller_StoreByRequest($clipartStorage, $clipartDstType, 80);
$clipartBig->addAction(new Primage_Proxy_Action_Resize(400, 400));
$clipartBig->addAction($watermarkAction);
$router->addController($clipartBaseUri . 'big.' . $clipartDstType, $clipartBig);

$clipartMedium = new Primage_Proxy_Controller_StoreByRequest($clipartStorage, $clipartDstType, 80);
$clipartMedium->addAction(new Primage_Proxy_Action_Resize(200, 200));
$clipartMedium->addAction($watermarkAction);
$router->addController($clipartBaseUri . 'medium.' . $clipartDstType, $clipartMedium);

$clipartThumb = new Primage_Proxy_Controller_StoreByRequest($clipartStorage, $clipartDstType, 80);
$clipartThumb->addAction(new Primage_Proxy_Action_Resize(100, 100));
$router->addController($clipartBaseUri . 'small.' . $clipartDstType, $clipartThumb);

/**************************************************************
	PROCESS REQUESTED IMAGE
 **************************************************************/

$controller = $router->getController($_SERVER['REQUEST_URI'], &$params);
if($controller) {
	$controller->dispatch($params);
	header('Location: ' . $_SERVER['REQUEST_URI']);
}
else {
//	header("HTTP/1.0 404 Not Found");
}
