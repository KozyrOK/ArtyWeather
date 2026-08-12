<p align="center">
  <a href="./README.en.md">🇬🇧 English</a> |
  <strong>🇷🇺 Русский</strong>
</p>

---

# ArtyWeather

**ArtyWeather** — PET-проект на Laravel для получения практического опыта разработки современных веб-приложений, работы с внешними REST API, кеширования, асинхронной обработки, Vue.js, Chart.js и интеграции локальной LLM.

Приложение получает прогноз погоды для заданной пользователем географической точки через бесплатный **Open-Meteo API** и отображает нормализованные погодные данные через интерфейс Vue.js.

Локальная LLM **Ollama** используется исключительно как **AI Presentation Layer**. Она не является источником погодных данных и не заменяет детерминированную логику приложения, определяющую погодное состояние.

## Цели проекта

- Освоить современную архитектуру Laravel-приложения
- Научиться работать с внешним REST API
- Реализовать модульный монолит
- Применять Service Layer и инфраструктурные абстракции
- Нормализовать ответы внешнего API во внутренние DTO/domain-структуры приложения
- Реализовать Laravel Cache и Redis
- Реализовать асинхронную обработку с помощью Laravel Queue
- Создать frontend на Vue.js 3
- Визуализировать временные ряды погодных данных с помощью Chart.js
- Интегрировать локальную LLM через Ollama
- Реализовать структурированный AI output и строгую валидацию
- Реализовать graceful degradation при недоступности внешних сервисов
- Создать PET-проект, готовый для представления в портфолио

## Основной архитектурный принцип

ArtyWeather явно разделяет **фактические погодные данные** и **AI-генерируемое представление**.

```text
Open-Meteo
    ↓
WeatherNormalizer
    ↓
WeatherSnapshot
    ↓
WeatherCondition
    ↓
AI Presentation Layer
    ↓
Ollama
    ↓
WeatherPresentation
    ↓
Vue.js
```

Ключевое правило:

> **Open-Meteo является источником фактических погодных данных. `WeatherSnapshot` представляет нормализованные факты. `WeatherCondition` определяется детерминированной логикой приложения. Ollama используется только как AI Presentation Layer и не должна изменять фактические погодные данные.**

## Технологический стек

**Backend**
- Laravel 13
- Laravel HTTP Client
- Laravel Cache
- Laravel Queue
- Laravel Sanctum
- PostgreSQL

**AI**
- Laravel AI SDK
- Ollama
- Локальная LLM

**Frontend**
- Vue.js 3
- Chart.js 4.x
- TailwindCSS

**Infrastructure**
- Docker
- Laravel Sail
- Redis
- Ubuntu

**External API**
- Open-Meteo

## Доменные / прикладные сущности

### Пользовательские настройки

У каждого пользователя есть одна активная конфигурация погоды, хранящаяся в PostgreSQL.

Конфигурация содержит:

- latitude
- longitude
- forecast period
- temperature
- apparent temperature
- relative humidity
- precipitation
- weather code
- cloud cover
- pressure
- wind speed
- wind direction
- wind gusts

Boolean-настройки погоды определяют **то, что отображается пользователю**, а не то, какие данные запрашиваются у Open-Meteo.

Например:

```text
temperature = true
pressure = true
wind_speed = false
```

означает, что приложение может получить все необходимые погодные данные, а frontend отображает температуру и давление, но не скорость ветра.

### WeatherSnapshot

`WeatherSnapshot` — нормализованное внутреннее представление фактических погодных данных.

Он содержит такие значения, как:

- latitude
- longitude
- timestamp
- temperature
- apparent temperature
- relative humidity
- precipitation
- weather code
- cloud cover
- pressure
- wind speed
- wind direction
- wind gusts

`WeatherSnapshot` не содержит AI-генерируемых данных.

### WeatherCondition

`WeatherCondition` — детерминированная семантическая классификация, рассчитываемая приложением.

Закрытый набор значений:

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

LLM не отвечает за определение этого значения.

### Season

Приложение использует закрытый набор времён года:

```text
SPRING
SUMMER
AUTUMN
WINTER
```

Время года определяется приложением на основании даты прогноза.

### Визуальные ассеты

ArtyWeather использует заранее подготовленные визуальные ассеты. Ассеты никогда не генерируются во время работы приложения.

Существует две основные категории:

```text
WeatherIcon
Landscape
```

#### WeatherIcon

Существует 8 предопределённых погодных пиктограмм:

```text
clear
partly_cloudy
cloudy
rain
heavy_rain
snow
fog
storm
```

#### Landscape

Приложение использует иллюстрации одной и той же живописной местности. Композиция остаётся неизменной, а время года и погодные условия меняются.

Полный набор содержит:

```text
8 WeatherCondition × 4 Season = 32 иллюстрации
```

Логические идентификаторы имеют формат:

```text
{season}_{weather_condition}
```

Примеры:

```text
spring_clear
summer_rain
autumn_fog
winter_snow
```

Физический путь к ассету никогда не генерируется и не возвращается LLM.

## AI Presentation Layer

Ollama является **единственным AI/LLM-провайдером**, используемым приложением.

AI-слой получает уже обработанные данные приложения:

```text
WeatherSnapshot
    +
WeatherCondition
    +
Season
```

и формирует валидированный `WeatherPresentation`.

### WeatherPresentation

Представление содержит:

```text
weather_condition
season
weather_icon
landscape
summary
recommendation
```

Пример:

```json
{
  "weather_condition": "RAIN",
  "season": "AUTUMN",
  "weather_icon": "rain",
  "landscape": "autumn_rain",
  "summary": "Ожидается дождливая осенняя погода.",
  "recommendation": "Рекомендуется взять зонт."
}
```

AI может выбирать только существующие идентификаторы ассетов.

AI не должен:

- генерировать изображения;
- генерировать SVG;
- генерировать имена файлов;
- генерировать URL;
- генерировать пути к файлам;
- придумывать новые идентификаторы ассетов;
- выступать источником погодных данных;
- изменять фактические погодные значения.

Весь структурированный AI output валидируется Laravel перед использованием.

## Backend-архитектура

Проект использует архитектуру **модульного монолита**.

### Presentation Layer

Отвечает за HTTP-запросы и API-ответы.

Примеры:

```text
routes/api.php
app/Http/Controllers/Api/
app/Http/Requests/
```

Контроллеры не должны содержать бизнес-логику.

### Application Layer

Основным orchestration-сервисом является:

```text
app/Services/WeatherService.php
```

Он отвечает за:

1. получение пользовательских настроек;
2. формирование параметров запроса погоды;
3. проверку кеша;
4. вызов клиента Open-Meteo при необходимости;
5. нормализацию ответа;
6. формирование `WeatherSnapshot`;
7. определение `WeatherCondition`;
8. подготовку ответа для frontend;
9. постановку AI presentation generation в очередь при необходимости.

### Infrastructure Layer

Доступ к Open-Meteo изолирован в:

```text
app/Infrastructure/Weather/OpenMeteoClient.php
```

Клиент отвечает за:

- формирование HTTP-запроса;
- координаты;
- период прогноза;
- погодные переменные;
- обработку timeout;
- retry;
- обработку HTTP-ошибок.

Код приложения не должен напрямую зависеть от структуры JSON Open-Meteo.

### WeatherNormalizer

```text
app/Services/Weather/WeatherNormalizer.php
```

Преобразует:

```text
Open-Meteo JSON
    ↓
WeatherSnapshot
```

Это сохраняет независимость приложения от формата ответа внешнего провайдера.

### AI Service

```text
app/Services/AI/AiWeatherPresentationService.php
```

Обязанности включают:

- формирование контекста для LLM;
- формирование AI prompt;
- вызов Ollama через Laravel AI integration;
- запрос структурированного output;
- валидацию результата;
- валидацию идентификаторов ассетов;
- формирование `WeatherPresentation`.

Сервис не получает погодные данные из Open-Meteo.

## Кеширование

Laravel Cache используется для уменьшения количества повторных запросов к Open-Meteo.

Ключ кеша погоды формируется на основании данных, влияющих на фактический запрос погоды, например:

```text
weather:{latitude}:{longitude}:{forecast_period}
```

Boolean-настройки отображения пользователя **не должны** входить в ключ кеша погоды, поскольку они влияют только на представление.

Это также позволяет разным пользователям, запрашивающим одну и ту же географическую точку и период прогноза, использовать общий подходящий кешированный результат погоды.

Для AI Presentation используется отдельный кеш. Его ключ может включать:

- координаты;
- период прогноза;
- hash нормализованных погодных данных;
- locale;
- версию presentation/asset schema при необходимости.

Если соответствующие погодные данные и контекст представления не изменились, приложение должно избегать ненужных вызовов Ollama.

## Асинхронная AI-обработка

Генерация AI Presentation выполняется асинхронно.

```text
WeatherService
    ↓
GenerateWeatherPresentationJob
    ↓
Redis Queue
    ↓
Queue Worker
    ↓
AiWeatherPresentationService
    ↓
Ollama
    ↓
WeatherPresentation
```

Job не должен напрямую обращаться к Open-Meteo.

Нормализованные погодные данные, необходимые AI-сервису, передаются через application layer.

Это предотвращает блокировку основного запроса погоды на время генерации AI-ответа.

## API

### `GET /api/weather`

Возвращает текущий прогноз погоды для настроенной точки авторизованного пользователя.

Координаты и период прогноза берутся из `WeatherSettings`.

Приложение может возвращать полный нормализованный набор погодных данных, а frontend отображает только параметры, включённые настройками пользователя.

### `POST /api/weather/refresh`

Принудительно обновляет погодные данные.

Логический процесс:

```text
POST /api/weather/refresh
    ↓
Invalidate weather cache
    ↓
Open-Meteo
    ↓
Normalize
    ↓
WeatherSnapshot
    ↓
WeatherCondition
    ↓
Response
```

### `GET /api/weather/presentation`

Возвращает состояние AI Presentation.

Пока генерация выполняется:

```json
{
  "status": "processing"
}
```

После завершения:

```json
{
  "status": "ready",
  "presentation": {
    "weather_condition": "RAIN",
    "season": "AUTUMN",
    "weather_icon": "rain",
    "landscape": "autumn_rain",
    "summary": "Ожидается дождливая осенняя погода.",
    "recommendation": "Рекомендуется взять зонт."
  }
}
```

## Аутентификация

Аутентификация реализована с помощью Laravel Sanctum.

Weather API использует авторизованного пользователя только для определения того, чьи `WeatherSettings` необходимо использовать.

Получение погоды при этом остаётся отделённым от логики аутентификации.

## Frontend

Frontend реализован на Vue.js 3.

Основные разделы приложения:

- **Weather Overview**
- **Dashboard**

### Weather Overview

Основной экран погоды содержит:

- текущую погоду;
- географическую точку;
- текущую температуру;
- WeatherCondition;
- WeatherIcon;
- выбранные погодные параметры;
- Weather Landscape;
- прогноз;
- погодные графики;
- AI Weather Presentation.

Отображаются только погодные параметры, включённые в `WeatherSettings`.

### Weather Charts

Chart.js 4.x используется для визуализации временных рядов погодных данных.

Основные графики:

- Temperature Chart
- Pressure Chart
- Wind Chart
- Precipitation Chart

Графики:

- используют нормализованные погодные данные;
- охватывают выбранный период прогноза;
- используют временную шкалу;
- отображают соответствующие единицы измерения;
- являются responsive;
- поддерживают интерактивное отображение значений точек;
- отображаются только при включённой соответствующей настройке;
- не содержат бизнес-логики.

### Dashboard

Dashboard позволяет пользователю настроить:

- latitude;
- longitude;
- forecast period;
- отображаемые погодные параметры;
- locale;
- theme.

Изменение настроек отображения не должно вызывать новый запрос погоды, если исходные погодные данные всё ещё актуальны.

### Состояния приложения

Frontend поддерживает:

- initial loading;
- weather loading;
- weather loaded;
- AI presentation processing;
- AI presentation ready;
- partial AI failure;
- Open-Meteo error;
- network error;
- invalid user settings;
- empty weather data.

Если Ollama недоступна, фактические погодные данные должны оставаться доступными.

Если AI-слой завершается ошибкой, backend может предоставить fallback-представление на основе `WeatherCondition` и `Season`.

### Адаптивный дизайн

Интерфейс поддерживает:

- desktop;
- tablet;
- mobile.

Карточки погоды, графики и иллюстрации Landscape должны оставаться читаемыми и удобными на небольших экранах.

## Обработка ошибок и отказоустойчивость

Приложение обрабатывает:

- timeout Open-Meteo;
- HTTP 4xx/5xx Open-Meteo;
- недоступность Open-Meteo;
- некорректные координаты;
- некорректные периоды прогноза;
- недоступность Ollama;
- AI timeout;
- ошибки очереди;
- отсутствие пользовательских настроек;
- некорректный structured output Ollama;
- недопустимые идентификаторы WeatherIcon;
- недопустимые идентификаторы Landscape.

Для запросов к внешнему API используется ограниченная стратегия retry.

Если Open-Meteo временно недоступен и существует подходящий кешированный результат, приложение может вернуть кешированные погодные данные с соответствующим статусом.

Если Ollama недоступна, фактические погодные данные должны оставаться доступными.

Генерация изображений во время работы приложения запрещена.

## Rate Limiting

Rate limiting следует применять к основным погодным endpoint'ам, особенно:

```text
/api/weather
/api/weather/refresh
/api/weather/presentation
```

Цель — предотвращение случайного чрезмерного количества запросов, защита внешнего погодного API и предотвращение повторной AI-генерации.

## Локализация и тема

Приложение поддерживает как минимум два языка интерфейса.

Приложение также поддерживает тёмную тему.

Локаль и тема являются частью пользовательских настроек и синхронизируются с frontend.

## План разработки

### Итерация 1 — Окружение

- Laravel 13
- Laravel Sail
- PostgreSQL
- Redis
- Sanctum
- базовая аутентификация
- Git

**Результат:** работающее backend-окружение с аутентификацией.

### Итерация 2 — Пользовательские настройки

- WeatherSettings
- координаты
- период прогноза
- параметры отображения
- API настроек
- Dashboard

**Результат:** пользователи могут настроить отображение погоды.

### Итерация 3 — Интеграция Open-Meteo

- OpenMeteoClient
- WeatherService
- WeatherNormalizer
- WeatherSnapshot
- WeatherConditionResolver
- WeatherCondition
- обработка HTTP-ошибок
- retry
- получение прогноза

**Результат:** нормализованные погодные данные и детерминированное погодное состояние.

### Итерация 4 — Cache

- Laravel Cache
- weather cache key
- TTL
- cache hit/miss
- принудительное обновление
- защита от повторных запросов

**Результат:** уменьшение количества обращений к внешнему API.

### Итерация 5 — Frontend

- Vue.js 3
- Weather Overview
- Dashboard
- Chart.js
- график температуры
- график давления
- график ветра
- график осадков
- визуализация прогноза
- выбранные погодные параметры
- WeatherIcon
- Landscape

**Результат:** полноценный интерфейс визуализации погоды.

### Итерация 6 — AI Presentation Layer

- интеграция Ollama
- AI prompt builder
- AiWeatherPresentationService
- GenerateWeatherPresentationJob
- Redis Queue
- Queue Worker
- structured output
- валидация AI response
- WeatherPresentation
- предопределённые визуальные ассеты
- AI summary
- AI recommendation
- AI Presentation cache
- fallback presentation
- интеграция с frontend

**Результат:** представление уже обработанных погодных данных с помощью локальной LLM.

### Итерация 7 — Полировка

- локализация
- тёмная тема
- rate limiting
- оптимизация кеша
- обработка ошибок
- тесты WeatherConditionResolver
- тесты WeatherPresentation
- тесты structured output
- тесты валидации идентификаторов ассетов
- тесты fallback
- документация
- screenshots

**Результат:** PET-проект, готовый для портфолио.

## Развёртывание

ArtyWeather рассчитан на запуск в контейнеризированном окружении разработки с использованием Docker и Laravel Sail.

### Требования

- Docker
- Docker Compose
- Git
- Ollama для AI Presentation Layer

### Окружение

Создайте файл окружения приложения на основе примера проекта:

```bash
cp .env.example .env
```

Настройте необходимые параметры приложения, PostgreSQL, Redis и Ollama в `.env`.

Модель Ollama управляется локальной установкой Ollama и не хранится в репозитории.

### Запуск приложения

Запустите окружение Sail:

```bash
./vendor/bin/sail up -d
```

Установите PHP-зависимости:

```bash
./vendor/bin/sail composer install
```

Установите frontend-зависимости:

```bash
./vendor/bin/sail npm install
```

Сгенерируйте ключ приложения:

```bash
./vendor/bin/sail artisan key:generate
```

Выполните миграции:

```bash
./vendor/bin/sail artisan migrate
```

Запустите frontend development server:

```bash
./vendor/bin/sail npm run dev
```

Соберите frontend для production:

```bash
./vendor/bin/sail npm run build
```

### Redis Queue Worker

Для генерации AI Presentation необходим запущенный queue worker.

Для локальной разработки:

```bash
./vendor/bin/sail artisan queue:work
```

### Ollama

Установите и управляйте Ollama отдельно от приложения.

Проверьте установку:

```bash
ollama --version
```

Проверьте доступные модели:

```bash
ollama list
```

Конкретная модель намеренно не фиксируется в этом README, поскольку техническое задание определяет **Ollama как единственного LLM-провайдера**, а конкретная локальная модель может быть настроена для конкретного окружения разработки.

Когда Laravel работает внутри Docker, а Ollama — на хост-машине, необходимо настроить приложение так, чтобы контейнер мог обращаться к API Ollama на хосте.

## Полезные команды

Остановить окружение:

```bash
./vendor/bin/sail down
```

Перезапустить окружение:

```bash
./vendor/bin/sail up -d
```

Просмотреть логи приложения:

```bash
./vendor/bin/sail logs
```

Получить доступ к контейнеру приложения:

```bash
./vendor/bin/sail shell
```

Выполнить миграции:

```bash
./vendor/bin/sail artisan migrate
```

Запустить тесты:

```bash
./vendor/bin/sail artisan test
```

Запустить queue worker:

```bash
./vendor/bin/sail artisan queue:work
```

## Итоговая архитектура

```text
                         ┌─────────────────┐
                         │    PostgreSQL   │
                         │                 │
                         │ User            │
                         │ WeatherSettings │
                         └────────┬────────┘
                                  │
                                  ↓
┌──────────┐              ┌─────────────────┐
│ Vue.js   │ ───────────→ │ WeatherService  │
└──────────┘              └────────┬────────┘
                                   │
                                   ↓
                            ┌───────────────┐
                            │ Cache         │
                            └───────┬───────┘
                                    │ MISS
                                    ↓
                          ┌──────────────────┐
                          │ OpenMeteoClient  │
                          └────────┬─────────┘
                                   ↓
                            ┌─────────────┐
                            │ Open-Meteo  │
                            └──────┬──────┘
                                   ↓
                         ┌──────────────────┐
                         │ WeatherNormalizer│
                         └────────┬─────────┘
                                  ↓
                         ┌─────────────────┐
                         │WeatherSnapshot  │
                         └───────┬─────────┘
                                 ↓
                    ┌─────────────────────────┐
                    │ WeatherConditionResolver│
                    └───────────┬─────────────┘
                                ↓
                       ┌────────────────┐
                       │WeatherCondition│
                       └───────┬────────┘
                               │
                               ↓
                 ┌──────────────────────────────┐
                 │GenerateWeatherPresentationJob│
                 └──────────────┬───────────────┘
                                ↓
                           Redis Queue
                                ↓
                          Queue Worker
                                ↓
                 ┌─────────────────────────────┐
                 │AiWeatherPresentationService │
                 └──────────────┬──────────────┘
                                ↓
                              Ollama
                                ↓
                         Structured JSON
                                ↓
                           Validation
                                ↓
                 ┌─────────────────────────┐
                 │   WeatherPresentation   │
                 └────────────┬────────────┘
                              │
               ┌──────────────┼──────────────┐
               ↓              ↓              ↓
        WeatherIcon       Landscape       Summary
               │              │              │
               └──────────────┼──────────────┘
                              ↓
                           Vue.js
                              ↓
                    Visual Presentation
```

## Ключевые архитектурные правила

1. **Open-Meteo является источником фактических погодных данных.**
2. **WeatherSnapshot содержит только нормализованные фактические данные.**
3. **WeatherCondition определяется приложением детерминированно.**
4. **Ollama является единственным LLM-провайдером.**
5. **Ollama является AI Presentation Layer, а не источником погодных данных.**
6. **AI получает нормализованные данные приложения, а не необработанный ответ внешнего API.**
7. **AI может выбирать только предопределённые идентификаторы WeatherIcon и Landscape.**
8. **AI-генерируемые идентификаторы ассетов всегда должны проходить валидацию.**
9. **Во время работы приложения изображения и новые визуальные ассеты не генерируются.**
10. **Boolean-настройки отображения пользователя не влияют на ключ кеша погоды.**
11. **Сбой AI не должен делать фактические погодные данные недоступными.**
12. **Frontend-компоненты не должны зависеть от исходной структуры ответа Open-Meteo или Ollama.**
