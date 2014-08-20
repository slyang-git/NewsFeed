<?php

require('lib/crawler/bbc.class.php');

//=====BBC中文网===============
$crawler = new BBCCrawler();
$url_seed_filename = 'urlseed/bbc.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	//防止空行
	if(empty($link)) continue; 
	echo 'Starting URL: ' . $link . PHP_EOL;
    $crawler->start($link);
}


?>