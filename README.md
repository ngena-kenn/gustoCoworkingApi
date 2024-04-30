# backend GustoCofee

Ce projet est une API de gestion des reservation dans un Coworking basée sur Symfony.

## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés :

- Symfony (version 6.1.12)
- PHP (version  8.2.6X)
- MariaDb (version 10.6.12) ou tout autre système de gestion de base de données pris en charge par Symfony

## Installation

1. Clonez ce dépôt vers votre machine locale :


2. Accédez au répertoire du projet :

    cd nom_du_projet
    
3. Installez les dépendances du projet en utilisant Composer :

    composer install

4. Configurez votre base de données en modifiant le fichier `.env` pour correspondre à vos paramètres de base de données :

    DATABASE_URL=mysql://user:password@host:port/database_name

5. Générez les migrations pour mettre à jour votre base de données :
   
    symfony console doctrine:migrations:migrate

6. Lancez le serveur de développement :

    symfony server:start