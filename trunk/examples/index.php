<?php

$sizes = array('big', 'medium', 'small');
$types['avatars'] = 'gif';
$types['clipart'] = 'jpg';
$images['avatars'] = array('sharapova', 'safin', 'federer');
$images['clipart'] = array('bird', 'girl');

foreach($images as $dir => $names) {
	foreach($names as $name) {
		$uris = array();
		foreach($sizes as $size) {
			$uri = 'images/' . $dir . '/' . $name . '_' . $size . '.' . $types[$dir];
			$uris[] = $uri;
			echo '<img src="' . $uri . '" /> ';
		}
		echo '<br />';
		foreach($uris as $uri) {
			echo '<a href="' . $uri . '" target="_blank">' . $uri . '</a><br />';
		}
		echo '<br />';
	}
}