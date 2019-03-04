# Install this poc with Docker

## Prerequisites
* Docker v18.09+
* docker-compose v1.23+
* Git


## Install containers
```bash
git clone git@github.com:jgauthi/poc_symfony3_fosrestbundle.git
cd poc_symfony3_fosrestbundle
```

This stack need [Traefik](https://traefik.io/) to work, you can use the file `docker-compose.traefik.yml` or your self reverse-proxy.

```bash
# Without traefik
docker-compose build

# With traefik
docker network create platform
docker-compose -f docker-compose.override.yml -f docker-compose.yml -f docker-compose.traefik.yml build
```

Finally, install php libraries and database.
```bash
# (optional) You can copy .env to .env.local and edit configuration
docker-compose exec php composer install
docker-compose exec php php bin/console doctrine:migrations:migrate

# Optional
docker-compose exec php php bin/console doctrine:fixtures:load
```


## Update HOST
You have to associate Traefik hosts on your host file.

```
# poc sf3 docker
127.0.0.1   platform.docker pma.docker maildev.docker
```

You can connect on url application:
* [Plaform symfony](http://platform.docker)
* [phpMyAdmin](http://pma.docker)
* [mailDev](http://maildev.docker)

## Usage
Launch docker containers:

```bash
# Without traefik
docker-compose up -d

# With traefik
docker-compose -f docker-compose.override.yml -f docker-compose.yml -f docker-compose.traefik.yml up -d
```


Enjoy
