<p align="center">
  <strong>🇬🇧 English</strong> |
  <a href="./README.ru.md">🇷🇺 Русский</a>
</p>

---

# ArtyWeather

**ArtyWeather** is a Laravel PET project for retrieving and presenting weather forecasts.

The application retrieves weather data from the free **Open-Meteo API**, normalizes it, and displays it through a **Vue.js 3** frontend.

A local **Ollama** LLM is additionally used as an **AI Presentation Layer**. AI does not determine factual weather conditions. It receives already processed application data and generates a short description, recommendation, and selection of predefined visual assets.

## Features

* weather forecast for configurable coordinates;
* configurable forecast period;
* selectable weather display parameters;
* weather data caching;
* Laravel Sanctum authentication;
* Vue.js 3 frontend;
* weather charts using Chart.js;
* deterministic `WeatherCondition`;
* local AI presentation through Ollama;
* structured JSON output with validation;
* predefined weather icons and landscape illustrations;
* fallback presentation when AI is unavailable;
* Redis and Laravel Queue;
* API rate limiting;
* localization and dark theme.

## Tech Stack

| Area           | Technologies                                               |
| -------------- | ---------------------------------------------------------- |
| Backend        | Laravel 13, PHP 8.3+, Laravel Sanctum, Laravel HTTP Client |
| Database       | PostgreSQL 18                                              |
| Cache / Queue  | Redis, Laravel Cache, Laravel Queue                        |
| Frontend       | Vue.js 3, Vite, Tailwind CSS 4, Chart.js 4                 |
| AI             | Ollama, local LLM                                          |
| Infrastructure | Docker, Laravel Sail                                       |
| Weather API    | Open-Meteo                                                 |

## Architecture

The project follows a modular monolith architecture.

The main architectural principle is the strict separation of factual weather data from AI presentation:

```text
Open-Meteo
     ↓
OpenMeteoClient
     ↓
WeatherNormalizer
     ↓
WeatherSnapshot
     ↓
WeatherConditionResolver
     ↓
WeatherCondition
     │
     ├──────────────→ Vue.js
     │
     ↓
AiWeatherPresentationService
     ↓
Ollama
     ↓
WeatherPresentation
     ↓
Vue.js
```

**Open-Meteo is the source of factual weather data. Ollama is used only for presentation and must not modify factual weather values.**

AI can select only predefined visual assets.

The application contains:

```text
8 WeatherCondition
×
4 Season
=
32 Landscape illustrations
```

No images are generated at runtime.

---

# Installation and Deployment

ArtyWeather runs in Docker using **Laravel Sail**.

PHP, Composer, Node.js, and PostgreSQL do not need to be installed directly on the host system for normal local development. They run inside the Docker environment.

## Requirements

The host system needs:

* Docker;
* Docker Compose;
* Git;
* Ollama, if the AI Presentation Layer is required.

A local Ollama model is also required for AI functionality.

---

## 1. Clone the repository

Clone the repository:

```bash
git clone https://github.com/KozyrOK/ArtyWeather.git
```

Enter the project directory:

```bash
cd ArtyWeather
```

---

## 2. Create the environment file

Create `.env` from the provided example:

```bash
cp .env.example .env
```

**Do not use `.env.example` as the runtime configuration file.**

`.env.example` is a template. Local configuration belongs in `.env`.

Check the main settings:

```dotenv
APP_NAME=ArtyWeather
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

APP_PORT=8080

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=artyweather
DB_USERNAME=sail
DB_PASSWORD=password

CACHE_STORE=redis

REDIS_HOST=redis
REDIS_PORT=6379

QUEUE_CONNECTION=redis
```

---

## 3. Configure AI

The AI Presentation Layer uses a local **Ollama** installation running **outside Docker**, on the host machine.

The `.env` AI configuration should contain:

```dotenv
########################################
# AI Configuration
########################################

AI_PROVIDER=ollama

OLLAMA_BASE_URL=http://host.docker.internal:11434
OLLAMA_MODEL=qwen3:8b
OLLAMA_TIMEOUT=30
OLLAMA_RETRIES=1

AI_PRESENTATION_CACHE_TTL=900

########################################
# Weather Presentation
########################################

WEATHER_PRESENTATION_QUEUE=weather-presentations
```

The variable names must match `config/ai.php`.

The settings are:

* `OLLAMA_BASE_URL` — Ollama address accessible from the Laravel container;
* `OLLAMA_MODEL` — model used by the application;
* `OLLAMA_TIMEOUT` — Ollama request timeout;
* `OLLAMA_RETRIES` — retry count;
* `AI_PRESENTATION_CACHE_TTL` — AI Presentation cache lifetime;
* `WEATHER_PRESENTATION_QUEUE` — queue name used by the Weather Presentation job.

`WEATHER_PRESENTATION_CACHE_TTL` should not be used because it is not part of the current application configuration.

---

## 4. Install and configure Ollama

Ollama is installed separately from the ArtyWeather Docker environment.

Verify the installation:

```bash
ollama --version
```

List installed models:

```bash
ollama list
```

If the configured model is not installed, download it:

```bash
ollama pull qwen3:8b
```

Check that Ollama responds:

```bash
curl http://localhost:11434/api/tags
```

The connection from Docker is:

```text
Laravel container
      ↓
host.docker.internal:11434
      ↓
Ollama on host
```

`host.docker.internal` is configured as a Docker host gateway in `compose.yaml`.

---

## 5. Install PHP dependencies

On a clean checkout, the `vendor` directory does not exist yet.

First start the Docker environment:

```bash
docker compose up -d --build
```

Then install Composer dependencies:

```bash
docker compose exec laravel.test composer install
```

Alternatively, after `vendor/bin/sail` becomes available:

```bash
./vendor/bin/sail composer install
```

---

## 6. Install frontend dependencies

Install npm dependencies:

```bash
./vendor/bin/sail npm install
```

---

## 7. Generate the application key

Run:

```bash
./vendor/bin/sail artisan key:generate
```

---

## 8. Run database migrations

PostgreSQL is provided by the `pgsql` Docker service.

Run:

```bash
./vendor/bin/sail artisan migrate
```

The database is now initialized.

---

## 9. Seed the database

For the standard initial dataset:

```bash
./vendor/bin/sail artisan db:seed
```

For an extended local development dataset:

```bash
./vendor/bin/sail artisan db:seed --class=DatabaseSeederTest
```

This step is optional.

---

# Starting the application

## 10. Start Docker services

The project defines three main services:

```text
laravel.test
pgsql
redis
```

Start them with:

```bash
./vendor/bin/sail up -d
```

Check their status:

```bash
./vendor/bin/sail ps
```

Stop them:

```bash
./vendor/bin/sail down
```

Restart them:

```bash
./vendor/bin/sail restart
```

---

## 11. Clear the configuration cache

After changing `.env`, clear the Laravel configuration cache:

```bash
./vendor/bin/sail artisan config:clear
```

This ensures that Laravel reads the current environment values.

---

## 12. Start Vite

For frontend development:

```bash
./vendor/bin/sail npm run dev
```

The default Vite port is:

```text
5173
```

It can be configured with:

```dotenv
VITE_PORT=5173
```

---

## 13. Build frontend assets

For a production build:

```bash
./vendor/bin/sail npm run build
```

---

# Application URL

By default, the application is available at:

```text
http://localhost:8080
```

The port is configured through:

```dotenv
APP_PORT=8080
```

and mapped by `compose.yaml`.

---

# PostgreSQL

PostgreSQL runs as a separate Docker container:

```text
pgsql:5432
```

Laravel connects to it using the Docker service name:

```dotenv
DB_HOST=pgsql
DB_PORT=5432
```

From the host system, PostgreSQL is exposed on:

```text
localhost:5433
```

The external port is configured through:

```dotenv
FORWARD_DB_PORT=5433
```

Do not use `localhost` as `DB_HOST` for Laravel. Inside Docker Compose, Laravel must connect to PostgreSQL through the `pgsql` service name.

---

# Redis

Redis is used for:

* application cache;
* sessions;
* AI Presentation cache;
* Laravel Queue.

Inside Docker:

```text
redis:6379
```

The main configuration is:

```dotenv
CACHE_STORE=redis

REDIS_HOST=redis
REDIS_PORT=6379

QUEUE_CONNECTION=redis
```

---

# Queue Worker

The project contains `GenerateWeatherPresentationJob`, which uses Laravel Queue with Redis.

Start a worker with:

```bash
./vendor/bin/sail artisan queue:work
```

For local development, keep the worker running in a separate terminal.

Check failed jobs with:

```bash
./vendor/bin/sail artisan queue:failed
```

### Current implementation note

The current `GET /api/weather/presentation` endpoint calls `AiWeatherPresentationService` directly during the HTTP request.

Therefore, a queue worker is **not currently required for this synchronous endpoint**.

`GenerateWeatherPresentationJob` is available for asynchronous AI Presentation processing.

---

# Caching

Weather data and AI Presentation use separate caches.

### Weather Cache

Configured through:

```dotenv
WEATHER_CACHE_TTL=900
```

This cache stores factual weather data obtained from Open-Meteo.

### AI Presentation Cache

Configured through:

```dotenv
AI_PRESENTATION_CACHE_TTL=900
```

This cache stores the generated AI Presentation.

Keeping these caches separate is intentional: changing or regenerating an AI presentation should not require another weather API request when the underlying weather data is still valid.

---

# Useful commands

### Docker

```bash
./vendor/bin/sail up -d
./vendor/bin/sail down
./vendor/bin/sail restart
./vendor/bin/sail ps
./vendor/bin/sail logs
```

### Laravel

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:status
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan route:list
```

### Tests

```bash
./vendor/bin/sail artisan test
```

### Queue

```bash
./vendor/bin/sail artisan queue:work
./vendor/bin/sail artisan queue:failed
```

### Shell

```bash
./vendor/bin/sail shell
```

---

# API

Main endpoints:

```text
POST   /api/auth/register
POST   /api/auth/login
GET    /api/auth/me
POST   /api/auth/logout

GET    /api/weather
POST   /api/weather/refresh
GET    /api/weather/presentation

GET    /api/weather-settings
PUT    /api/weather-settings
PATCH  /api/weather-settings
```

Weather and settings endpoints are protected by Laravel Sanctum.

---

# Project Structure

```text
app/
├── DTO/
│   ├── AI/
│   └── Weather/
├── Http/
│   ├── Controllers/Api/
│   ├── Requests/
│   └── Resources/
├── Infrastructure/
│   └── Weather/
└── Services/
    ├── AI/
    └── Weather/

resources/
├── js/
└── css/

routes/
├── api.php
└── web.php

config/
├── ai.php
└── services.php

compose.yaml
.env.example
```

---