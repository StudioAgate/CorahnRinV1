<?php
$_PAGE['layout'] = 'ajax';

Esterenchar::session_clear();

unset($_SESSION['amelio_bonus'], $_SESSION['bonusdom']);

Session::setFlash('Le personnage a été correctement réinitialisé !', 'success');
header('Location: ' . mkurl(array('val'=>62)));
exit;