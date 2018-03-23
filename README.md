mindsymfony
===========
A Symfony project created on January 26, 2018, 10:57 am.

## Installation
Commande lines:

```bash
git clone git@bitbucket.org:gauthij/mindsymfony.git
git-install-hook-pre-commit.sh
git-install-config-github-email.sh
composer install
php bin/console assets:install --symlink
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load
droit-linux-www.sh
````

For the asset symlink install, launch a terminal on administrator in windows environment.


## Prerequisites

* La version de PHP doit être supérieure ou égale à PHP 5.5.9 ;
* L'extension SQLite 3 doit être activée ;
* L'extension JSON doit être activée ;
* L'extension Ctype doit être activée ;
* Le paramètre _date.timezone_ doit être défini dans le php.ini.

--> [more info](https://symfony.com/doc/3.4/reference/requirements.html)

## Prepare deploy prod

* **Temporarily** edit the file web/app.php, change the 2e args to true: ``$kernel = new AppKernel('prod', true);`` and test the site on prod mode.
* Check prerequisites on prod server: [domain.com]/config.php (edit the file to edit/remove IP verification) OR command line: ``php bin/symfony_requirements``
* Configure apache symfony dir (virtual host on dev env) to **web/** folder.

## Deploy on prod

* Delete manualy "var/*/" content before send file (ftp)
* Chmod 777 recursive on prod, on folder: "var/"
* You can edit web/app_dev.php with personal IP to access dev environment on prod.
* If an 500 error occurs, check log on "var/logs/prod"

