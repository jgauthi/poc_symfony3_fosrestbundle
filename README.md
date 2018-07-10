Symfony Learning
===========
A Symfony project created on January 26, 2018, to learn the framework :-).

## Prerequisites

* The PHP version must be greater than or equal to PHP 5.5.9
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
    * Generate data Fixture (entity)
        * Advert
        * Category
        * Skill
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
    * Generate data Fixture (entity)
        * Some users with role (admin, author, user)

## Installation
Command lines:

```bash
git clone git@github.com:jgauthi/symfony3_learning.git
composer install

php bin/console assets:install --symlink
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load
````

For the asset symlink install, launch a terminal on administrator in windows environment.

## Prepare deploy prod

* **Temporarily** edit the file web/app.php, change the 2e args to true: ``$kernel = new AppKernel('prod', true);`` and test the site on prod mode.
* Check prerequisites on prod server: [domain.com]/config.php (edit the file to edit/remove IP verification) OR command line: ``php bin/symfony_requirements``
* Configure apache symfony dir (virtual host on dev env) to **web/** folder.

## Deploy on prod

* Delete manualy "var/*/" content before send file (ftp)
* Chmod 755 recursive on prod, on folder: "var/"
* You can edit web/app_dev.php with personal IP to access dev environment on prod.
* If an 500 error occurs, check log on "var/logs/prod"

