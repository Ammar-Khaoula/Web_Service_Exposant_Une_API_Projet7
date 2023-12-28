
# Projet 7 BileMeo

Créez un web service exposant une API





## Environnement utilisé durant le développement

- Symfony 6.4
- Composer 2.5.8
- willdurand/hateoas-bundle
- lexik/jwt-authentication-bundle
- nelmio/api-doc-bundle
- XAMPPServer: ->control panel:3.33.0 ->Apache: 2.4.5 ->Mysql: 5.2.1 ->PHP: 8.1




## Installation

1.Clonez ou téléchargez le repository GitHub dans le dossier voulu :

```bash
git clone https://github.com/Ammar-Khaoula/Web_Service_Exposant_Une_API_Projet7.git

```
2.Editez le fichier situé à la racine intitulé .env.local qui devra être crée à la racine du projet en réalisant une copie du fichier .env afin de remplacer les valeurs de paramétrage de la base de données:

```bash
//Exemple : mysql://root:@127.0.0.1:3306/API_projet7
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

```
3.Installez les dépendances back-end du projet avec Composer:

```bash
composer install

```
4.Créez la base de données, taper la commande ci-dessous en vous plaçant dans le répertoire du projet:

```bash
symfony console doctrine:database:create

```
5.Créez les différentes tables de la base de données en appliquant les migrations:

```bash
symfony console doctrine:migrations:migrate

```
6.Après avoir créer votre base de données, vous pouvez également injecter un jeu de données en effectuant la commande suivante:

```bash
symfony console doctrine:fixtures:load

```
7.Générer des clés SSL pour le token JWT:

```bash
php bin/console lexik:jwt:generate-keypair

```
8.Test :

```bash
Allez sur http://127.0.0.1:8000/api/doc et suivez les instructions

```
    
