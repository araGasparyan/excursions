# Excursions RESTful API
The software is a web-based application developed for museum excursions, which encodes rules of the excursions management. The software provides tools for managing attributes of the excursions like language, guide, initiator, etc., and tracks the timing information related to excursion lifecycle (like registration, actual arrival, start and end time of an excursion). The queue management system Included in the program ensures load balancing for guides and organize exhibition in a smart way.


## Setup
(1) Change directory into the root of the project:
```sh
cd excursions
```

(2) Set appropriate time zone from the file **public\index.php** by the function **date_default_timezone_set**.

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

## License
Copyright © 2021, [Ara Gasparyan](https://aragasparyan.com).
Released under the [MIT License](https://opensource.org/licenses/MIT).
