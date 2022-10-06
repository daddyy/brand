# README

## install
- mysql + php.8.1 + composer libs
### DIR
1. `$ mkdir ./cache ./logs`
2. `$ chmod 755 .`
3. `$ chmod 775 ./cache ./logs`
4. create / copy config file
    - production: `$ cp ./app/config/config.yml.tmp ./app/config/config.yml`
    - development: `$ cp ./app/config/config.yml.tmp ./app/config/devel.config.yml`
    - local: `$ cp ./app/config/config.yml.tmp ./app/config/local.config.yml`
5. `$ composer install && composer update`
### server service
- see the conf.d/ and setup the http service and php

### first run
- `app/src/install/check.php` => put (uncomment) it the require to the `run.php` after the init object config
- fill the DB with example data
### SQL dump
- `app/src/install/seed.php` => put (uncomment) it the require to the `run.php` after the init object config
- fill the DB with example data
