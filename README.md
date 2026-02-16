# 🚒 Sistema de Gestão de Ocorrências — Corpo de Bombeiros

## 📌 Visão Geral

Este projeto implementa um sistema crítico de gestão de ocorrências para o Corpo de Bombeiros, projetado com foco em:

* **Resiliência operacional**
* **Processamento assíncrono**
* **Auditabilidade**
* **Isolamento de responsabilidades**
* **Escalabilidade horizontal**

A arquitetura utiliza containers Docker para segmentação clara entre camadas de aplicação, persistência, mensageria e processamento assíncrono.

---

## 🏗️ Arquitetura da Solução

A infraestrutura é composta pelos seguintes serviços:

| Serviço       | Responsabilidade                  |
| ------------- | --------------------------------- |
| **app**       | Backend Laravel (PHP 8.3-FPM)     |
| **webserver** | Nginx (Reverse Proxy)             |
| **db**        | PostgreSQL 15                     |
| **redis**     | Filas, Cache e Atomic Locks       |
| **worker**    | Processamento assíncrono de filas |

### 🔄 Fluxo de Processamento

1. A API recebe a ocorrência.
2. A requisição é persistida no banco.
3. Um evento é publicado na fila (Redis).
4. O **Worker** consome e processa a ocorrência.
5. Logs e auditorias são registrados para rastreabilidade.

---

## ⚙️ Setup do Ambiente (Task 01)

### 1️⃣ Pré-requisitos

* Docker
* Docker Compose
* Porta `8000` disponível (Nginx)
* Porta `5432` disponível (PostgreSQL)

---

### 2️⃣ Configuração

```bash
cp .env.example .env
```

Configurações obrigatórias no `.env`:

```
DB_HOST=db
REDIS_HOST=redis
```

---

### 3️⃣ Provisionamento da Infraestrutura

```bash
docker-compose up -d --build
```

O comando:

* Constrói imagens customizadas
* Inicializa todos os serviços
* Executa containers em modo detached

---

### 4️⃣ Inicialização da Aplicação

```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

---

### 5️⃣ Observabilidade

Monitoramento em tempo real:

```bash
docker-compose logs -f
```

Logs críticos:

* Execução do Worker
* Conexão com Redis
* Execução de migrations
* Erros HTTP da API

---

## 🔐 Considerações Técnicas

* Processamento desacoplado via fila
* Banco relacional com suporte a transações
* Uso de locks atômicos via Redis
* Arquitetura preparada para escalabilidade do worker
* Separação entre camada web e aplicação

---

## 🚀 Diferenciais Técnicos (Bônus)

* **Resiliência:** Retry automático com backoff exponencial para falhas de integração.
* **Dead-Letter Queue:** Tratamento de falhas definitivas via tabela de `failed_jobs`.
* **Performance:** Cache de leitura dinâmico no Redis para listagem de ocorrências.
* **Segurança:** Bloqueio de concorrência com Atomic Locks no Redis.

---

## 🛠️ Testando a API

### Criar Ocorrência (Integração)
**POST** `/api/integrations/occurrences`
*Header:* `X-API-Key: sua_chave` | `Idempotency-Key: uuid`

### Fluxo Operacional
* **Iniciar:** `/api/occurrences/{id}/start`
* **Resolver:** `/api/occurrences/{id}/resolve`
* **Despachar Viatura:** `/api/occurrences/{id}/dispatches`

---

### 🧪 Testes Automatizados
```bash
docker-compose exec app php artisan test
```

----

## 🌐 Endpoint

Sistema disponível em:

```
http://localhost:8000
```

---

## 📎 Conclusão

A solução atende requisitos de:

* Alta confiabilidade
* Auditabilidade completa
* Processamento resiliente
* Separação clara de responsabilidades
* Facilidade de deploy via containerização

Projeto preparado para evolução futura (monitoramento, métricas, autenticação, CI/CD).
