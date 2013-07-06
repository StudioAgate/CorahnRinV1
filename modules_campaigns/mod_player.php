<?php

$game_player = isset($_PAGE['request']['player']) ? (int) $_PAGE['request']['player'] : 0;

if (!$game_player) {
	Session::setFlash('Une partie doit être sélectionnée', 'error');
	return;
}

echo 'Partie personnelle';