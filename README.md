Corahn-Rin, le générateur de personnages
========================================

Cette web-application vous permet d'étendre votre expérience de jeu pour le jeu de rôle [Les Ombres d'Esteren](http://www.esteren.org/).

Le site est accessible ici : https://jdr.pierstoval.com/esteren/fr

:warning: Vous avez le droit d'utiliser et de modifier cette repository, mais pas d'en faire une utilisation commerciale.

:warning: Tous les contenus relatifs au jeu de rôle Les Ombres d'Esteren écrits dans cette repository appartiennent à Agate Éditions, et aucune reproduction de ces contenus n'est autorisée sans l'accord explicite de leurs auteurs.
Ces contenus incluent tous les textes écrits dans chacune des 20 étapes de création de personnage.

# Installation

Utilisez Docker Compose (compatible toutes plateformes) :

```
# Installe un fichier de config par défaut (et fonctionnel)
$ cp config.php.dist config.php

# Télécharger les images & les démarrer
$ docker-compose up -d

# Installer la base de données d'exemple
$ docker-compose exec mysql bash install_database.bash
```

# Debug mode

Pour activer le mode debug, ouvrez le fichier `config.php` et placez-y ceci si ce n'est pas déjà fait :

```php
define('P_DEBUG', true);
```
 
Pour activer le mode debug lors de la création de personnage, faites en sorte que `showMsg` soit systématiquement égal à `true` dans [webroot/js/main.js](webroot/js/main.js#L33), de cette façon, à chaque modification du personnage via AJAX, la réponse du serveur sera affichée dans la page.
En plus de ça, vous pouvez voir l'actuelle valeur de l'étape dans le personnage en cours de création, il suffit de remplacer `/*` par `//*` à [cet endroit dans le module `create_char`](modules/mod_create_char.php#L65)

# Administration
----------------

Le compte administrateur est `admin`, le mot de passe est le même. Si votre compte comporte un niveau d'ACL minimum, vous aurez normalement accès à toute l'administration. Attention à ne pas tout modifier !

Pour toute question, envoyez un mail à pierstoval@gmail.com !

# Bugs

Utiliser le [bug tracker](https://github.com/StudioAgate/CorahnRinV1/issues) de Github.

Enjoy!
