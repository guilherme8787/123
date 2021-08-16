## Bem vindo

Teste realizado no Apache2, rodando em um amazon-linux-2

Framework usado: Lumen (8.2.4) (Laravel Components ^8.0)

Versão do PHP: 8.0.8 

## Requisitos 

PHP >= 7.3

OpenSSL PHP Extension

PDO PHP Extension

Mbstring PHP Extension

## Instalando


$ mkdir 123

$ cd 123

$ git clone https://github.com/guilherme8787/123.git

$ cd 123

$ composer install


### Altere o arquivo .env no diretorio 123 (caso esteja em localhost não sera necessario alterar):
APP_URL=http://localhost

### Execute o comando para rodar em uma instalação local
$ php -S localhost:8000

### Abra o navegador e acesse para testar
http://localhost:8000/public/api/flights/groups

## Documentação da API
-> https://documenter.getpostman.com/view/14359832/TzshG4ic

## URLs para teste
-> https://appeconomy.com.br/flights/123/public/api/flights/groups
