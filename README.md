# Waterpolo news api

[Live demo](https://waterpolo.nevercodealone.de)

A Never Code Alone Playground

## Setup
- Symfony 5
- Webpack encore
- Bootstrap

## Symfony training for testing and refactoring
[Workshops for PHP and Javascript](https://nevercodealone.de/de/php-training)

### Run commands
```bash
$ yarn run encore dev --watch
$ yarn run dev
$ yarn run build
```

### Import commands
```bash
$ bin/console app:import:content firstDomain
```

firstDomain is a debug parameter for only running the first domain

### Testing
```bash
$ vendor/bin/php-cs-fixer fix src --dry-run --diff
```
