# atintranet

## Requirements

### Production

* `PHP` >= 7.3

### Development

* `composer`
* `nodejs` >= 6.0
* `yarn`
* `PHP` >= 7.3
    
## Installation

### Production

1. Copy `deploy/hosts.yaml.dist` to `deploy/hosts.yaml`
2. Customize `deploy/hosts.yaml`
3. Run `vendor/bin/dep deploy prod`

### Development

1. Clone repository
2. Run `make install`
3. Run `make start`
4. Run `make prepare`

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

## TODO

* Add action to duplicate an invoice
* Add feature: offers
