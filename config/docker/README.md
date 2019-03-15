# Install this poc with Docker

## Prerequisites
* Docker v18+ / docker-compose v1.23+
* _(for windows users)_ [Make command](https://stackoverflow.com/questions/32127524/how-to-install-and-use-make-in-windows/54086635)
* Git


## Install containers
Before use the docker version, check that ports 80/8080/443 are available. If an Apache / Nginx local server, another docker container are active, they can block access to these ports.

```bash
git clone git@github.com:jgauthi/poc_symfony3_fosrestbundle.git
cd poc_symfony3_fosrestbundle
make install
# (optional) You can copy .env to .env.local and edit configuration
```

Finally, install database.
```bash
make db-migrate

# Optional
make db-fixtures
```


## Update HOST
This stack install [Traefik](https://traefik.io/) to work, you can use this reverse-proxy. In this case, you have to associate Traefik hosts on your host file.

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
make start
```

For additional make command, you can use `make help`. 

Enjoy
