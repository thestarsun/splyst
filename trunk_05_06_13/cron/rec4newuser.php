<?php
$ch = curl_init();
$url = 'http://207.198.114.205/default/recommendation/recforsignupuser?id='.$argv[1].'&token=bnmsdferw678ghjhewr2347611';
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.91 Safari/534.30');
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/'); 
curl_setopt($ch, CURLOPT_USERPWD, 'admin:SPLyst1010'); 
var_dump(curl_exec($ch));
curl_close($ch);
//file_get_contents('http://127.0.0.17/default/recommendation/recforsignupuser?id='.$argv[1].'&token=bnmsdferw678ghjhewr2347611');
?>
