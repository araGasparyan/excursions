## Short description of excursions
This is a RESTful API for managing excursions in the Matenadaran


## Setup
(1) Change directory into the root of the project:
```sh
cd excursions
```

(2) Make the project a git repo:
```sh
git init
git add .
git commit -m "Initial commit"
```

(3) Run the code below to copy the environment variables file template, and setup all the values:
```sh
cp example.env .env
```

(4) Install PHP 7 or more (see http://php.net/manual/en/install.php for instructions).

(5) Download composer.phar 1.8.0 or more to your project root (see https://getcomposer.org/download/). Go to the file php.ini and uncomment extensions disable-tls and mbstring. In order to get required packages run:
```sh
./composer.phar install
```
As a result the file composer.lock will be created as well (You can commit that change).

(6) Set up Docker 18.09.1 and more (see https://docs.docker.com/engine/installation/ for instructions). After the installation check if you have docker-compose 1.23.2 or more.

(7) To start application run:
```sh
$ docker-compose up --build
```

(8) Run development migration by:
```sh
$ docker exec -t excursions_frontend_1 ./vendor/bin/phinx migrate -e production
```

(9) In order to run all the tests let us run this command:
```sh
$ docker exec -t excursions_frontend_1 ./vendor/bin/phpunit ./tests/
```


## Migration management
In order to create a new migration via phinx run:
```sh
$ docker exec -t excursions_frontend_1 ./vendor/bin/phinx create NewMigration
```

In order to rollback a migration via phinx run:
```sh
$ docker exec -t excursions_frontend_1 ./vendor/bin/phinx rollback -e production -t 20190930124802
```
