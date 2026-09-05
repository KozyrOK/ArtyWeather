<p align="center">
  <a href="./README.en.md">🇬🇧 English</a> |
  <strong>🇷🇺 Русский</strong>
</p>

---

# ArtyWeather

**ArtyWeather** — PET-проект на Laravel для получения и визуального представления прогноза погоды.

Приложение получает данные через бесплатный **Open-Meteo API**, нормализует их и отображает во frontend на **Vue.js 3**.

Дополнительно используется локальная LLM **Ollama** как **AI Presentation Layer**. AI не определяет фактическую погоду, а только формирует текстовое описание, рекомендацию и выбирает визуальные ассеты на основе уже подготовленных приложением данных.

## Возможности

* прогноз погоды для заданных координат;
* настраиваемый период прогноза;
* выбор отображаемых погодных параметров;
* кеширование погодных данных;
* авторизация через Laravel Sanctum;
* Vue.js 3 frontend;
* погодные графики на Chart.js;
* детерминированный `WeatherCondition`;
* локальная AI-презентация через Ollama;
* structured JSON output с валидацией;
* предопределённые погодные иконки и иллюстрации;
* fallback при недоступности AI;
* Redis и Laravel Queue;
* rate limiting;
* локализация и тёмная тема.

## Стек

| Область        | Технологии                                                 |
| -------------- | ---------------------------------------------------------- |
| Backend        | Laravel 13, PHP 8.3+, Laravel Sanctum, Laravel HTTP Client |
| Database       | PostgreSQL 18                                              |
| Cache / Queue  | Redis, Laravel Cache, Laravel Queue                        |
| Frontend       | Vue.js 3, Vite, Tailwind CSS 4, Chart.js 4                 |
| AI             | Ollama, локальная LLM                                      |
| Infrastructure | Docker, Laravel Sail                                       |
| Weather API    | Open-Meteo                                                 |

## Архитектура

Проект построен как модульный монолит.

Основной принцип — фактические погодные данные полностью отделены от AI-презентации:

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

**Open-Meteo является источником фактических погодных данных. Ollama не определяет погоду и не изменяет её числовые значения.**

AI может выбирать только существующие визуальные ассеты.

Используется:

```text
8 WeatherCondition
×
4 Season
=
32 Landscape illustrations
```

Новые изображения во время работы приложения не генерируются.

---

# Установка и развёртывание

ArtyWeather запускается в Docker через **Laravel Sail**.

Для локальной разработки не требуется устанавливать PHP, Composer, Node.js или PostgreSQL непосредственно в систему. Они работают внутри контейнера Laravel и связанных сервисов.

## Требования

На хост-системе необходимы:

* Docker;
* Docker Compose;
* Git;
* Ollama — если требуется AI Presentation Layer.

Для AI также необходима установленная локальная модель Ollama.

---

## 1. Клонирование проекта

Клонируйте репозиторий:

```bash
git clone https://github.com/KozyrOK/ArtyWeather.git
```

Перейдите в каталог проекта:

```bash
cd ArtyWeather
```

---

## 2. Подготовка `.env`

Создайте локальный файл конфигурации:

```bash
cp .env.example .env
```

**Не редактируйте `.env.example` для локальной конфигурации.**

`.env.example` используется как шаблон. Рабочие значения находятся в `.env`.

Перед запуском проверьте основные параметры:

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

## 3. Конфигурация AI

AI Presentation Layer использует локальную **Ollama**, которая запускается **вне Docker**, непосредственно на хост-компьютере.

В `.env` должны быть указаны:

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

Названия переменных должны соответствовать `config/ai.php`.

В частности:

* `OLLAMA_BASE_URL` — адрес Ollama для Laravel-контейнера;
* `OLLAMA_MODEL` — используемая модель;
* `OLLAMA_TIMEOUT` — timeout обращения к Ollama;
* `OLLAMA_RETRIES` — количество retry;
* `AI_PRESENTATION_CACHE_TTL` — TTL кеша AI Presentation;
* `WEATHER_PRESENTATION_QUEUE` — имя очереди для AI Presentation Job.

`WEATHER_PRESENTATION_CACHE_TTL` использовать не следует: эта переменная не является частью текущей конфигурации приложения.

---

## 4. Установка Ollama

Ollama устанавливается отдельно от Docker-контейнеров ArtyWeather.

После установки проверьте:

```bash
ollama --version
```

Посмотрите доступные модели:

```bash
ollama list
```

Если требуемой модели нет, загрузите её:

```bash
ollama pull qwen3:8b
```

Проверьте, что Ollama отвечает:

```bash
curl http://localhost:11434/api/tags
```

Важный момент для Docker:

```text
Laravel container
      ↓
host.docker.internal:11434
      ↓
Ollama on host
```

`host.docker.internal` уже добавлен в `compose.yaml` как Docker host gateway.

---

## 5. Установка PHP-зависимостей

На чистом checkout директории `vendor` ещё нет.

Есть два варианта.

### Вариант A — через Docker после запуска Sail

Сначала запустите контейнеры:

```bash
docker compose up -d --build
```

После этого установите Composer dependencies:

```bash
./vendor/bin/sail composer install
```

Если `vendor/bin/sail` ещё отсутствует, используйте bootstrap через Docker Compose:

```bash
docker compose run --rm laravel.test composer install
```

После установки `vendor/bin/sail` станет доступен.

---

## 6. Установка frontend-зависимостей

После установки PHP dependencies:

```bash
./vendor/bin/sail npm install
```

---

## 7. Генерация application key

Выполните:

```bash
./vendor/bin/sail artisan key:generate
```

---

## 8. Миграции базы данных

PostgreSQL уже запускается как отдельный Docker-сервис.

Выполните:

```bash
./vendor/bin/sail artisan migrate
```

После успешного выполнения миграций база данных приложения готова.

---

## 9. Заполнение базы тестовыми данными

Для обычной инициализации:

```bash
./vendor/bin/sail artisan db:seed
```

Для расширенного набора тестовых данных:

```bash
./vendor/bin/sail artisan db:seed --class=DatabaseSeederTest
```

Если тестовые данные не нужны, этот шаг можно пропустить.

---

# Запуск приложения

## 10. Запуск Docker-сервисов

Основные сервисы проекта определены в `compose.yaml`:

```text
laravel.test
pgsql
redis
```

Запуск:

```bash
./vendor/bin/sail up -d
```

Проверка состояния:

```bash
./vendor/bin/sail ps
```

Остановка:

```bash
./vendor/bin/sail down
```

Перезапуск:

```bash
./vendor/bin/sail restart
```

---

## 11. Очистка конфигурационного кеша

После изменения `.env` рекомендуется очистить кеш конфигурации:

```bash
./vendor/bin/sail artisan config:clear
```

Если Laravel ранее использовал закешированную конфигурацию, это гарантирует повторное чтение актуальных значений из `.env`.

---

## 12. Frontend в режиме разработки

Запустите Vite:

```bash
./vendor/bin/sail npm run dev
```

По умолчанию Vite использует порт:

```text
5173
```

Порт можно изменить через:

```dotenv
VITE_PORT=5173
```

---

## 13. Production frontend build

Для production-сборки:

```bash
./vendor/bin/sail npm run build
```

---

# Доступ к приложению

По умолчанию приложение доступно по адресу:

```text
http://localhost:8080
```

Порт задаётся:

```dotenv
APP_PORT=8080
```

В `compose.yaml` он пробрасывается в контейнер Laravel.

---

# PostgreSQL

PostgreSQL работает внутри отдельного контейнера:

```text
pgsql:5432
```

Laravel подключается к нему по имени Docker-сервиса:

```dotenv
DB_HOST=pgsql
DB_PORT=5432
```

Для подключения к PostgreSQL с хост-системы по умолчанию используется:

```text
localhost:5433
```

Внешний порт определяется:

```dotenv
FORWARD_DB_PORT=5433
```

Не следует указывать `localhost` в `DB_HOST` для Laravel-контейнера. Внутри Docker Compose Laravel должен обращаться к PostgreSQL по имени сервиса `pgsql`.

---

# Redis

Redis используется для:

* application cache;
* session storage;
* AI presentation cache;
* Laravel Queue.

Внутри Docker:

```text
redis:6379
```

Основные параметры:

```dotenv
CACHE_STORE=redis

REDIS_HOST=redis
REDIS_PORT=6379

QUEUE_CONNECTION=redis
```

В `compose.yaml` Redis также может быть доступен с хоста через порт:

```text
6379
```

Проверить работу Redis можно командой:

```bash
./vendor/bin/sail artisan tinker
```

---

# Queue Worker

В проекте присутствует `GenerateWeatherPresentationJob`, использующий Redis Queue.

Для обработки очереди запустите:

```bash
./vendor/bin/sail artisan queue:work
```

Для локальной разработки worker можно оставить работающим в отдельном терминале.

Проверить очередь можно через:

```bash
./vendor/bin/sail artisan queue:failed
```

### Важное замечание

Текущий `GET /api/weather/presentation` вызывает `AiWeatherPresentationService` непосредственно в рамках HTTP-запроса.

Поэтому Queue Worker **не является обязательным условием для текущего синхронного вызова этого endpoint**.

`GenerateWeatherPresentationJob` существует как инфраструктура для асинхронной обработки AI Presentation.

---

# Cache

Погодные данные и AI Presentation используют отдельные кеши.

### Weather Cache

Управляется настройкой:

```dotenv
WEATHER_CACHE_TTL=900
```

Кеш относится к фактическим данным Open-Meteo.

### AI Presentation Cache

Управляется:

```dotenv
AI_PRESENTATION_CACHE_TTL=900
```

Этот кеш относится только к результату AI Presentation.

Разделение этих TTL важно: изменение AI-презентации не должно приводить к повторному запросу погодных данных у Open-Meteo.

---

# Полезные команды

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

Основные endpoints:

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

Weather и settings endpoints защищены Laravel Sanctum.

---

# Структура проекта

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
