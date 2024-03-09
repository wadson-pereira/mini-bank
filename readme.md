# Bank Account Management

## Stack

- Laravel 10+
- PHP 8.1
- Docker & ocker-compose
- Prometheus
- Grafana

### O que foi implementado:
-   Listar produtos disponíveis
-   Cadastrar nova venda
-   Consultar vendas realizadas


### Como rodar o proejeto


Subir os serviços

    docker-compose up -d
Rodar as migrations


    docker-compose exec app php artisan migrate

Executar as seeds

    docker-compose exec app php artisan db:seed

Executar os testes

    docker-compose exec app php artisan test

Comandos adicionais:

Na raiz do projeto existe um makefiel com algumas ferramentas úteis: phpcbf, phpstan, phpcs  
exemplo: `make test`

### Métricas

Configurar um novo datasource  com o prometheus  na rota

    http://localhost:3000/connections/datasources/new
    - prometheus server url: http://prometheus:9090
    - HTTP method: get
