<?php
unset($filename);
header('Content-type:text/plain');
$cnt = $_PAGE['content_for_layout'];

$cnt = preg_replace('#<head>[^§]+</head>#isUu', '', $cnt);
$cnt = preg_replace('#<div class="container" id="navigation">[^§]+<!-- /div\#navigation\.container -->#isUu', '', $cnt);

echo $cnt;