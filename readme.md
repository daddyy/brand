# README

## install
- mysql + php.8.1 + composer libs
- see the conf.d/ and setup the http service and php

- you can run the install ./build/install

### DIR
1. `$ mkdir ./cache ./logs`
2. `$ chmod 755 .`
3. `$ chmod 775 ./cache ./logs`
4. create / copy config file
    - production: `$ cp ./src/config/config.yml.tmp ./src/config/config.yml`
    - development: `$ cp ./src/config/config.yml.tmp ./src/config/devel.config.yml`
    - local: `$ cp ./src/config/config.yml.tmp ./src/config/local.config.yml`
5. `$ composer update`
### first run
```bash
    php ./www/index.php -fcheck
```
```bash
    php ./www/index.php -fdb
```
- fill the DB with example data