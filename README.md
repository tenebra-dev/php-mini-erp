# 📦 PHP MINI ERP

Este projeto é um mini ERP construído em **PHP** com **MySQL**, utilizando **Docker** e **Apache** para facilitar o ambiente de desenvolvimento.

## 🚀 Como rodar a aplicação com Docker

### 🔽 Derrubar containers, imagens e volumes

Use este comando para **derrubar completamente** os containers, imagens e volumes criados:

```
docker-compose -f docker/docker-compose.yml down
```

### 📌 Explicação das flags:

-f docker/docker-compose.yml: especifica o caminho do arquivo docker-compose.yml

down: derruba os containers

--rmi all: remove todas as imagens

--remove-orphans: remove containers "órfãos" que não estão mais definidos

-v: remove os volumes associados (como banco de dados)

--timeout 0: encerra imediatamente

### 🔽 Subir os Conteineres

```
docker-compose -f docker/docker-compose.yml up --build -d
```

### 📌 Explicação das flags:

--force-recreate: força recriação dos containers mesmo que nada tenha mudado

--build: força o rebuild das imagens

-d: sobe em modo "detached" (segundo plano)

## 📡 Endpoints da API

A API estará disponível em:
http://localhost:8080

Exemplos:
GET /users – lista usuários (exemplo de rota configurada)

## 📌 Requisitos

Docker
Docker Compose
(Opcional) HTTPie ou curl

## ⚙️ Instalação de dependências

Ao rodar via Docker, as dependências PHP são instaladas automaticamente no container.  
Se quiser rodar localmente (fora do Docker), execute:

```
composer install
```

## 🔑 Configuração de variáveis de ambiente

Copie o arquivo `.env.example` para `.env` e preencha com seus dados:

```sh
cp .env.example .env
```

Você pode configurar variáveis sensíveis (SMTP, banco, etc) em um arquivo `.env` na raiz do projeto.  
O container carrega essas variáveis automaticamente se você usar o [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv).

Exemplo de `.env`:
```
SMTP_HOST=smtp.seuprovedor.com
SMTP_USER=usuario@dominio.com
SMTP_PASS=senha
SMTP_PORT=587
MAIL_FROM=no-reply@dominio.com
```

## 🧪 Testes automatizados

Para rodar os testes (dentro do container):

```
vendor/bin/phpunit
```

Ou, se estiver fora do container:

```
docker-compose exec app vendor/bin/phpunit
```

## 📡 Exemplos de requisições API

### Criar produto
```sh
curl -X POST http://localhost:8000/api/products -d '{"name":"Produto Teste","price":10.0}' -H "Content-Type: application/json"
```
