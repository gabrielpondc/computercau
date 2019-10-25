<?php
$cardno = $_GET['cardno'];
$url="http://220.248.75.36/handapp/PGcardAmtServlet?arg1=$cardno";
$html = file_get_contents($url);
$result = explode("'",$html);
//var_dump($result);
echo $result[1];
