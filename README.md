Corahn-Rin, le générateur de personnages
========================

Cette web-application vous permet d'étendre votre expérience de jeu pour le jeu de rôle [Les Ombres d'Esteren](http://www.esteren.org/).

Le site est accessible ici : http://jdr.pierstoval.com/esteren/fr

:warning: Vous avez le droit d'utiliser et de modifier cette repository, mais pas d'en faire une utilisation commerciale.

:warning: Tous les contenus relatifs au jeu de rôle Les Ombres d'Esteren écrits dans cette repository appartiennent à Agate Éditions et sont créés par l'association Forgesonges, et aucune reproduction de ces contenus n'est autorisée sans l'accord explicite de leurs auteurs.
Ces contenus incluent tous les textes écrits dans chacune des 20 étapes de création de personnage.


1) Installation
----------------------------------

Installez l'application sur votre serveur (local ou distant).

Copiez et collez le fichier `db.php` à partir du fichier `db.php.dist` à la racine pour bien y marquer les données de connexion à votre base de données.

Une fois fait, la structure de la base de données est située dans `files/install.sql`, vous devez importer manuellement ce fichier pour obtenir les données de base du générateur.

2) Debug mode
--------------------------------

Vérifier que le debug mode est bien actif dans [app.php](app.php#L113).
Pour activer le mode debug lors de la création de personnage, faites en sorte que `showMsg` soit systématiquement égal à `true` dans [webroot/js/main.js](webroot/js/main.js#L52), de cette façon, à chaque modif du personnage via AJAX, la réponse du serveur sera affichée dans la page.
En plus de ça, vous pouvez voir l'actuelle valeur de l'étape dans le personnage en cours de création, il suffit de supprimer le commentaire de [ce code, dans le module `create_char`](modules/mod_create_char.php#L60-L67)

3) Administration
--------------------------------

Le compte administrateur est `admin`, le mot de passe est le même. Si votre compte comporte un niveau d'ACL minimum, vous aurez normalement accès à toute l'administration. Attention à ne pas tout modifier !

Pour toute question, envoyez un mail à pierstoval@gmail.com !

4) Bugs
--------------------------------

Vous pouvez rapporter un bug de deux façons différentes :

* Utiliser le [bug tracker](//github.com/Pierstoval/Esteren/issues) de Github.
* Utiliser la plateforme Redmine, le gestionnaire de projets que l'équipe d'Esteren utilise pour gérer tous les projets de la gamme, en créant un nouveau ticket directement via ce lien : http://redmine.pierstoval.com/projects/corahn-rin-v1/issues/new

Enjoy!
