# Mini bank API

## Requistos

Requisitos:

1. Criar endpoint e comando CLI para criação de conta bancária


2. Cada conta deve ter um identificador (que pode ser o id sequencial), nome e saldo (que começa zerado).


3. Criar endpoint (sem comando) para movimentação de fundos entre duas contas


4. Deve aceitar remetente, destinatário e valor, em JSON

5. Opcionalmente, pode ser programada para uma data futura

6. Deve consultar o serviço autorizador externo no momento do processamento. Caso não seja autorizado, a transação não
   deve ser processada. (documentação do serviço logo abaixo)

```
{

"success":  true,

"authorized":  true  // ou false

}
````

8. A conta não pode ficar negativa


9. As transações agendadas devem ser processadas todos os dias, às 05h da manhã

10. Testes automatizados

## Stack

- Laravel 10+
- Mysql
- PHP 8.1
- Docker & docker-compose
- Prometheus
- Granfana

### Como rodar o projeto
Subir os serviços

    docker-compose up -d
Rodar as migrations


    docker-compose exec app php artisan migrate

Executar as seeds

    docker-compose exec app php artisan db:seed

Executar os testes

    docker-compose exec app php artisan test

### Comandos adicionais dos requisitos

Comando de criação de contas

    docker-compose exec app php artisan app:create-bank-account ana 1

Comando de processamento de contas

    docker-compose exec app php artisan app:process-scheduled-transaction

Comando para listar processamentos agendados

    docker-compose exec app php artisan  schedule:list 

Comando para rodar todos os processamentos agendados

    docker-compose exec app php artisan schedule:run 

Comandos adicionais:

Na raiz do projeto existe um makefiel com algumas ferramentas úteis: phpcbf, phpstan, phpcs  
exemplo: `make test`

### Métricas

As métricas estão sendo expostas na rota `localhost:8080/metrics` , o prometheus vai subir coletando as métricas.
Não consegui colocar o grafrana para subir com um dashboard, mas caso desejem testar sigam os passos:

Configurar um novo datasource  com o prometheus  na rota

    http://localhost:3000/connections/datasources/new
    - prometheus server url: http://prometheus:9090
    - HTTP method: get

Importar um novo dashbord

    http://localhost:3000/dashboard/import
    - Utilizar o arquivo json na pasta do projeto `.docker-configs/grafana/dashboards/dashboard.json`

### Collections

    Na raiz do projeto tem uma pasta /collection que contém as collections para teste no insominia 

### Melhorias

Atualmente quando um processamento falha, não é registrado que o mesmo falhou. Esse seria um ponto de melhoria pois se o
comando rodar mais de uma vez por dia a mesma transação que falhou será reprocessada e falhará novamente.

Utilizar factories para gerar as entities.

Tratamento dos valores de entradas dos comandos para processamento de transações e criação de contas.

Melhorar mensagem de saida dos comandos de criação de contas e processamento de transações.
