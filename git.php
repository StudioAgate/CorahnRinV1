<?php
if (Users::$id !== 0) {
	return;
}

echo '<pre>';
system('git --git-dir ".git" status');
echo '</pre>';