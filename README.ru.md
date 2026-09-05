<p align="center">
  <a href="./README.en.md">🇬🇧 English</a> |
  <strong>🇷🇺 Русский</strong>
</p>

---

# ArtyWeather

**ArtyWeather** — PET-проект на Laravel, который получает прогноз погоды через бесплатный **Open-Meteo API** и отображает его в интерфейсе на **Vue.js 3**.

Проект также демонстрирует интеграцию локальной LLM **Ollama** как отдельного **AI Presentation Layer**: модель получает уже нормализованные погодные данные и формирует краткое описание и рекомендацию. Фактические погодные данные определяются не AI, а приложением на основе ответа Open-Meteo.

## Возможности

- получение прогноза для заданных координат;
- настраиваемый период прогноза;
- выбор отображаемых погодных параметров;
- кеширование погодных данных в Redis;
- авторизация через Laravel Sanctum;
- Weather Overview и Dashboard на Vue.js 3;
- графики погодных временных рядов через Chart.js;
- детерминированное определение `WeatherCondition`;
- локальная AI-презентация через Ollama;
- структурированный JSON output с валидацией;
- заранее подготовленные `WeatherIcon` и `Landscape`;
- fallback-представление при ошибке AI;
- rate limiting для API.

## Стек

| Область | Технологии |
|---|---|
| Backend | Laravel 13, PHP 8.3+, Laravel Sanctum, Laravel HTTP Client, Cache, Queue |
| Database | PostgreSQL 18 |
| Cache / Queue | Redis |
| Frontend | Vue.js 3, Vite, Tailwind CSS 4, Chart.js 4, vue-chartjs |
| AI | Ollama, локальная LLM |
| Infrastructure | Docker, Laravel Sail |
| Weather API | Open-Meteo |

## Архитектура

Проект построен как модульный монолит с разделением получения погодных данных и их презентации.

```text
Vue.js
   ↓
WeatherController
   ↓
WeatherService
   ↓
Redis Cache
   │
   └── MISS → OpenMeteoClient → Open-Meteo
                              ↓
                       WeatherNormalizer
                              ↓
                       WeatherSnapshot
                              ↓
                  WeatherConditionResolver
                              ↓
                       WeatherCondition

WeatherSnapshot + WeatherCondition
                ↓
      AiWeatherPresentationService
                ↓
              Ollama
                ↓
        validated WeatherPresentation
                ↓
              Vue.js
```

Ключевой принцип: **Open-Meteo является источником фактических погодных данных. Ollama отвечает только за их презентацию и не может изменять фактические значения или придумывать новые визуальные ассеты.**

### Основные внутренние структуры

`WeatherSnapshot` — нормализованные погодные данные приложения.

`WeatherCondition` — детерминированное семантическое состояние погоды:

```text
CLEAR
PARTLY_CLOUDY
CLOUDY
RAIN
HEAVY_RAIN
SNOW
FOG
STORM
```

`WeatherPresentation` содержит:

```text
weather_condition
season
weather_icon
landscape
summary
recommendation
```

Визуальные ассеты заранее определены. Используются 8 погодных иконок и 32 варианта `Landscape` (`8 conditions × 4 seasons`). Во время работы приложения новые изображения или идентификаторы ассетов не генерируются.

## AI Presentation Layer

Вызов Ollama изолирован в `app/Services/AI/OllamaWeatherPresentationClient.php`. AI-сервис получает `WeatherSnapshot`, `WeatherCondition` и `Season`, после чего запрашивает JSON с четырьмя полями: `weather_icon`, `landscape`, `summary`, `recommendation`.

Приложение дополнительно проверяет ответ: `weather_icon` и `landscape` должны совпадать с разрешёнными значениями для текущих условий. При ошибке или недоступности Ollama используется детерминированный fallback.

Важно: текущий `GET /api/weather/presentation` получает или генерирует presentation непосредственно в рамках HTTP-запроса. `GenerateWeatherPresentationJob` и Redis Queue присутствуют в проекте, но endpoint в текущем состоянии не возвращает промежуточный статус `processing`.

## API

Все weather/settings endpoints требуют `auth:sanctum` и ограничены `throttle:api`.

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

Погодные настройки пользователя включают координаты, период прогноза и набор boolean-флагов отображения. Эти флаги определяют представление результата, а не набор фактических данных, получаемых от Open-Meteo.

## Запуск через Laravel Sail

### Требования

- Docker и Docker Compose;
- Git;
- Ollama — только для AI Presentation Layer.

Laravel Sail используется как основное окружение приложения. `compose.yaml` поднимает три сервиса: Laravel, PostgreSQL и Redis. Ollama в compose-файл не входит и предполагается запущенным на хост-машине. Контейнер Laravel обращается к нему через `host.docker.internal`.

### Установка

Клонируйте репозиторий и перейдите в каталог проекта:

```bash
git clone https://github.com/KozyrOK/ArtyWeather.git
cd ArtyWeather
```

Создайте `.env`:

```bash
cp .env.example .env
```

Перед запуском убедитесь, что Ollama установлена на хосте и нужная модель загружена.

### Запуск

На чистом checkout зависимости Composer и npm отсутствуют, поэтому сначала установите их. Выполните:

```bash
./vendor/bin/sail composer install
./vendor/bin/sail npm install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail up -d
```

После запуска откройте:

```text
http://localhost:8080
```

Порт `8080` задан в `.env.example` через `APP_PORT=8080`. PostgreSQL внутри Sail доступен приложению на `pgsql:5432`; наружу он проброшен на `5433` по умолчанию. Redis доступен приложению как `redis:6379`.

### Frontend

Для Vite в режиме разработки:

```bash
./vendor/bin/sail npm run dev
```

Порт Vite берётся из `VITE_PORT` (по умолчанию `5173`). Для production-сборки:

```bash
./vendor/bin/sail npm run build
```

### Ollama

Ollama работает **вне Docker**, на хост-машине.

Проверка:

```bash
ollama --version
ollama list
```

Актуальный `config/ai.php` использует следующие переменные окружения:

```text
AI_PROVIDER=ollama
OLLAMA_BASE_URL=http://host.docker.internal:11434
OLLAMA_MODEL=...
OLLAMA_TIMEOUT=30
OLLAMA_RETRIES=1
```

> В текущем `.env.example` пока указаны `OLLAMA_URL` и `AI_MODEL`, тогда как `config/ai.php` читает `OLLAMA_BASE_URL` и `OLLAMA_MODEL`. Для работы AI значения `.env` необходимо привести в соответствие с `config/ai.php`.

### Queue worker

В проекте есть `GenerateWeatherPresentationJob`, использующий Redis Queue. Worker запускается командой:

```bash
./vendor/bin/sail artisan queue:work
```

При этом текущий `GET /api/weather/presentation` вызывает `AiWeatherPresentationService` непосредственно, поэтому worker не требуется для самого этого endpoint.

### Полезные команды

```bash
./vendor/bin/sail up -d
./vendor/bin/sail down
./vendor/bin/sail restart
./vendor/bin/sail logs
./vendor/bin/sail shell
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
```

## Конфигурация по умолчанию

При отсутствии `WeatherSettings` приложение создаёт настройки по умолчанию:

```text
latitude:         55.75583
longitude:        37.61722
forecast_period: 7
```

Все доступные погодные параметры отображения по умолчанию включены.

## Структура проекта

```text
app/
├── DTO/
│   ├── AI/
│   └── Weather/
├── Http/
│   ├── Controllers/Api/
│   ├── Requests/
│   └── Resources/
├── Infrastructure/Weather/
└── Services/
    ├── AI/
    └── Weather/

resources/
├── js/          # Vue.js
└── css/         # Tailwind CSS

routes/
├── api.php
└── web.php

compose.yaml
.env.example
```

## Дополнительная документация

Подробное архитектурное и техническое описание проекта находится в [`Technical_specifications.md`](./Technical_specifications.md).
