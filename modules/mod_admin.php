<?php

if (P_LOGGED === false) {
    redirect(['val' => 48]);
}

?><div class="container"><h3><?php tr("Bienvenue sur le panneau d'administration"); ?></h3></div><?php

buffWrite('css', '');
buffWrite('js', '');
