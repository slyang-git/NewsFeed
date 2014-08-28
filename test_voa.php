<?php

require('lib/crawler/voa.class.php');

//=====VOA中文网===============
$crawler = new VOACrawler();
$url_seed_filename = 'urlseed/voa.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	//防止空行
	if(empty($link)) continue; 
	echo 'Starting URL: ' . $link . PHP_EOL;
    $crawler->start($link);
}


?>