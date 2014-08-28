<?php

require('lib/crawler/dw.class.php');

//=====德国之声中文网===============
$crawler = new DWCrawler();
$url_seed_filename = 'urlseed/dw.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	//防止空行
	if(empty($link)) continue; 
	echo 'Starting URL: ' . $link . PHP_EOL;
    $crawler->start($link);
}


?>