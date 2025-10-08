Corahn-Rin, le générateur de personnages
========================================

Cette web-application vous permet d'étendre votre expérience de jeu pour le jeu de rôle [Les Ombres d'Esteren](http://www.esteren.org/).

Le site est accessible ici : https://jdr.pierstoval.com/esteren/fr

:warning: Vous avez le droit d'utiliser et de modifier cette repository, mais pas d'en faire une utilisation commerciale.

:warning: Tous les contenus relatifs au jeu de rôle Les Ombres d'Esteren écrits dans cette repository appartiennent à Agate Éditions, et aucune reproduction de ces contenus n'est autorisée sans l'accord explicite de leurs auteurs.
Ces contenus incluent tous les textes écrits dans chacune des 20 étapes de création de personnage.

# Pré-requis pour le dev

- Docker
- Make
- De préférence un système sous Linux (ou Windows + WSL)

# Installation

Utilisez `make`:

```
$ make install
```

Cette commande va :

* Créer un fichier `config.php` avec les informations par défaut pour faire fonctionner le projet
* Télécharger les images Docker nécessaires
* Démarrer les containers
* Installer les dépendances Composer
* Installer une base de données fonctionnelle, avec un compte administrateur `admin` (mot de passe `admin`), les informations nécessaires pour utiliser Corahn-Rin, ainsi que le personnage Yldiane par défaut pour tester l'export de la feuille de personnage en PDF.

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
