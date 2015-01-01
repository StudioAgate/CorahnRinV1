Corahn-Rin, le générateur de personnages
========================

Cette web-application vous permet d'étendre votre expérience de jeu pour le jeu de rôle [Les Ombres d'Esteren][3]

Vous avez le droit d'utiliser et de modifier cette repo, mais pas d'en faire une utilisation commerciale.

1) Installation
----------------------------------

Installez l'application sur votre serveur (local ou distant).

Modifiez le fichier `db.php` à la racine pour bien y marquer les données de connexion à votre base de données.

Une fois fait, la structure de la base de données est située dans `files/install.sql`, vous devez importer manuellement ce fichier pour obtenir les données de base du générateur.

2) Htaccess
-------------------------------------

Il y a deux fichiers `.htaccess` dans cette application. L'un se trouve à la racine, l'autre dans le dossier `webroot`.
Assurez-vous que l'attribut `RewriteBase` correspond bien à l'éventuel sous-dossier dans lequel est installée l'application.
Par défaut, c'est dans `/esteren`, mais si vous stockez cette appli à la racine du serveur, la base sera `/`.
Ces fichiers assurent que l'utilisateur ne puisse pas se rendre dans un des dossiers de développement.

3) Administration
--------------------------------

Le compte administrateur est `admin`, le mot de passe est le même.

Pour toute question, envoyez un mail à pierstoval@gmail.com !

Enjoy!

[3]:  http://www.esteren.org/
