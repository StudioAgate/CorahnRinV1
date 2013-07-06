<?php
$_PAGE['layout'] = 'ajax';

$ajax_page = isset($_PAGE['request'][0]) ? $_PAGE['request'][0] : '';

if ($ajax_page) {
	load_module($ajax_page, 'module');
}