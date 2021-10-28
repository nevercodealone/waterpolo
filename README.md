# Waterpolo news api

A Never Code Alone Playground

## Setup
- Symfony 5
- Webpack encore
- Bootstrap

### Run commands
```bash
$ yarn run encore dev --watch
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
