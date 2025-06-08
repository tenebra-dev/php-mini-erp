# 📦 PHP REST API com Docker

Este projeto é uma API REST simples construída em **PHP** com **MySQL**, utilizando **Docker** e **Apache** para facilitar o ambiente de desenvolvimento.

## 🚀 Como rodar a aplicação com Docker

### 🔽 Derrubar containers, imagens e volumes

Use este comando para **derrubar completamente** os containers, imagens e volumes criados:

```
bash docker-compose -f docker/docker-compose.yml down
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

## 🧪 Testando a API

Você pode usar ferramentas como:

httpie:

```
http GET http://localhost:8080/users
```

curl:

```
curl http://localhost:8080/users
```
