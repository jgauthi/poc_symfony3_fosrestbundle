POC Symfony 3 FosRestBundle
===========

## Prerequisites

* The PHP version must be greater than or equal to PHP 7.2
* The SQLite 3 extension must be enabled
* The JSON extension must be enabled
* The Ctype extension must be enabled
* The date.timezone parameter must be defined in php.ini

More information on [symfony website](https://symfony.com/doc/3.4/reference/requirements.html).

## Features developed in bundles

* **PlatformBundle**: Online advert management
    * Controller
        * List / add / edit / delete advert
        * List of applications for an advert
    * Features / Configuration used
        * Generate data Fixture (entity): Advert, Category, Skill (with [Nelmio Alice](https://github.com/nelmio/alice))
        * Test units on advert / application entities
    * Services
        * Antispam: Check if the messages are more than 50 characters, otherwise it will be considered like spam.
        * ApplicationMailer: Send an mail to author's advert when a candidate register.
        * Beta: Displays a yellow banner indicating the number of days remaining before the end of the beta.
        * Bigbrother: Send an email to admin if some users post a message.
        * CustomParamConverter: Display param converter from url request.
        * MarkdownTransformer: Convert markdown to html. 
* **MyUserBundle**: User management with the fos/user-bundle
    * Controller
        * Login / logout / forgotten password...
        * Role management
    * Features / Configuration used
        * Generate data Fixture (entity): Some users with role (admin, author, api access, user)
        * Entity management with the [EasyAdminBundle](https://symfony.com/doc/master/bundles/EasyAdminBundle/index.html) (Symfony BackOffice)
        * In BackOffice, export entity data to csv file with [serializer component](https://symfony.com/doc/3.4/components/serializer.html)
* **MyRestApi**: Add Rest API implementation with fos/rest-bundle
    * Controller
        * CRUD
            * [GET] Get advert or application data
            * [POST] Add advert or application with validation data
            * [PATCH] Update some fields in advert
            * [PUT] Update all fields in advert
            * [DELETE] Remove advert
    * Features / Configuration used
        * Query String (QueryParam & ParamFetcher)
        * API Documentation: Using [NelmioApiDocBundle](https://symfony.com/doc/2.x/bundles/NelmioApiDocBundle/index.html) v2.x to generate online doc with Annotations (ApiDoc & FosRest).
        * Security: Authentication by token _(X-Auth-Token)_


## Installation WITHOUT docker
Command lines:

```bash
git clone git@github.com:jgauthi/poc_symfony3_fosrestbundle.git
cd poc_symfony3_fosrestbundle

composer install

# (optional) You can edit configuration values "app/config/parameters.yml"

php bin/console assets:install --symlink
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate

# Optional
php bin/console doctrine:fixtures:load
```

For the asset symlink install, launch a terminal on administrator in windows environment.


## Installation with docker-compose

```bash
git clone git@github.com:jgauthi/poc_symfony3_fosrestbundle.git
cd poc_symfony3_fosrestbundle

docker-compose up -d
docker-compose exec php composer install

# Copy ".env.dist" to ".env"
# (optional) You can edit configurations values ".env" and "app/config/parameters.yml"

docker-compose exec php php bin/console assets:install --symlink
docker-compose exec php php bin/console doctrine:database:create --if-not-exists
docker-compose exec php php bin/console doctrine:migrations:migrate

# Optional
docker-compose exec php php bin/console doctrine:fixtures:load
```

## [Docker-compose] Application urls
This docker-compose use a reverse proxy: [Traefik](https://traefik.io/), url supported:

* [Plaform symfony](http://platform.docker)
* [phpMyAdmin](http://pma.docker)
* [mailDev](http://maildev.docker)



## Prepare deploy prod

* **Temporarily** edit the file web/app.php, change the 2e args to true: ``$kernel = new AppKernel('prod', true);`` and test the site on prod mode.
* Check prerequisites on prod server: [domain.com]/config.php (edit the file to edit/remove IP verification) OR command line: ``php bin/symfony_requirements``
* Configure apache symfony dir (virtual host on dev env) to **web/** folder.

## Deploy on prod

* Delete manualy "var/*/" content before send file (ftp)
* Chmod 755 recursive on prod, on folder: "var/"
* You can edit web/app_dev.php with personal IP to access dev environment on prod.
* If an 500 error occurs, check log on "var/logs/prod"

