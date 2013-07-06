<?php
$_PAGE['layout'] = 'xml';

if (isset($_PAGE['request'][0]) && $_PAGE['request'][0] === 'style') {
	load_module('style', 'module');
	return;
}
if (!empty($_PAGE['request'])) {
	redirect(array('ext'=>'xml'));
}
load_module('sitemap', 'module');
