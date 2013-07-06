<?php
if (Users::$id !== 0) {
	return;
}

echo '<pre>';
system('git status');
echo '</pre>';