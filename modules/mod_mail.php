<?php

$type = isset($_PAGE['request'][0]) ? $_PAGE['request'][0] : '';

load_module($type, 'module');