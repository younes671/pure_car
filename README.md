Projet Symfony - Gestion des Réservations
Bienvenue dans le projet Pure car, location de voiture électrique ! Ce projet est une application web développée avec Symfony, permettant la gestion des réservations pour des véhicules électrique, ainsi que l'administration des entités associées telles que les marques, les modèles, les catégories, et les véhicules.

Table des Matières
Introduction
Prérequis
Installation
Configuration
Utilisation
Structure du Projet
Tests
Contribuer
Licence
Introduction
Ce projet est une application Symfony qui permet aux utilisateurs de réserver et louer des véhicules électriques. Les fonctionnalités principales incluent :

Gestion des entités pour l'administrateur du site : Ajout, modification et suppression de marques, modèles, catégories et véhicules.
Réservation de véhicules : Réservation en ligne pour les utilisateurs connectés et non connectés.
Envoi de confirmations par email : Confirmation automatique des réservations via email.
Prérequis
Avant de commencer, assurez-vous d'avoir les éléments suivants installés :

PHP (version 8.0 ou supérieure)
Composer
Symfony CLI (optionnel mais recommandé)
MySQL ou un autre système de gestion de base de données compatible
Installation
Pour installer le projet, suivez ces étapes :

Clonez le dépôt 

Accédez au répertoire du projet 

Installez les dépendances :

composer install

Créez la base de données :

php bin/console doctrine:database:create

Exécutez les migrations :

php bin/console doctrine:migrations:migrate

Créez un utilisateur pour accéder à l'application (si nécessaire) 

Démarrez le serveur de développement Symfony :

symfony server:start
ou si vous n'avez pas Symfony CLI :

php bin/console server:run

Configuration

Le projet utilise des fichiers de configuration standard pour Symfony. Les principales configurations se trouvent dans le dossier config/.

.env : Contient les variables d'environnement pour la configuration de la base de données et d'autres services.
config/packages : Contient la configuration des différents bundles utilisés dans le projet.
Utilisation
Accéder à l'application
Une fois le serveur démarré, vous pouvez accéder à l'application à l'adresse suivante :

http://localhost:8000

Fonctionnalités

Gestion des entités en mode ADMIN : Créez, modifiez ou supprimez des marques, modèles, catégories et véhicules via le panneau d'administration.
Réservation : Effectuez des réservations de véhicules en ligne. Les utilisateurs connectés bénéficient d'une interface personnalisée.
Confirmation par email : Recevez un email de confirmation après chaque réservation.

Structure du Projet
Voici un aperçu de la structure du projet :

/pur_car
|-- /bin
|-- /config
|-- /public
|-- /src
|   |-- /Controller
|   |-- /Entity
|   |-- /Form
|-- /templates
|-- /translations
|-- /var
|-- /vendor
|-- .env
|-- composer.json
|-- symfony.lock
|-- README.md
/src/Controller : Contient les contrôleurs de l'application.
/src/Entity : Contient les entités Doctrine.
/src/Form : Contient les formulaires Symfony.
/templates : Contient les templates Twig.
/translations : Contient les fichiers de traduction.
/var : Contient les fichiers générés par Symfony, comme les logs et le cache.
/public : Contient les fichiers accessibles publiquement comme les images et le JavaScript.

Les contributions sont les bienvenues ! Veuillez suivre ces étapes pour contribuer :

Forkez le dépôt.
Créez une nouvelle branche (git checkout -b feature/YourFeature).
Faites vos modifications et ajoutez des tests si possible.
Committez vos modifications (git commit -am 'Add new feature').
Poussez la branche (git push origin feature/YourFeature).
Ouvrez une Pull Request.
Licence
Ce projet est sous la licence MIT. Voir le fichier LICENSE pour plus de détails.

