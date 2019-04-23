# atintranet

## Requirements

### Production

* `PHP` >= 7.2

### Development

* `composer`
* `nodejs` >= 6.0
* `yarn`
* `PHP` >= 7.2
    
## Installation

### Production

1. Copy `deploy/hosts.yaml.dist` to `deploy/hosts.yaml`
2. Customize `deploy/hosts.yaml`
3. Run `vendor/bin/dep deploy prod`

### Development

1. Clone repository
2. Run `composer install`
3. Copy `.env` to `.env.local`
4. Customize `.env.local`
5. Run `php bin/console doctrine:schema:create` to upgrade database schema
6. Run `php bin/console doctrine:fixtures:load -n` to upgrade database schema
7. Run `yarn install` to install CSS/JS vendors
8. Run `yarn dev` to compile CSS/JS (or `yarn watch` to reload CSS/JS in realtime)
9. Run `php bin/console server:start` to start local web server (see Symfony's documentation)

### Tests (phpstan & phpunit)

1. Maybe you want to customize `.env.test`
2. Run
```
vendor/bin/phpstan analyse src --level 1 \
&& php doctrine:database:drop --force -e test \
&& php doctrine:schema:create -e test \
&& php doctrine:fixtures:load -n -e test \
&& vendor/bin/simple-phpunit \
&& php doctrine:database:drop --force -e test
```

## Changelog

* See [CHANGELOG](CHANGELOG.md)

## TODO

* Add action to duplicate an invoice
* Add feature: offers
