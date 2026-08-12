# Technical specifications — project ArtyWeather

# 1. Общая идея проекта

**ArtyWeather** — веб-приложение (PET-проект) для получения практического опыта разработки современных веб-приложений с интеграцией внешнего API и локальной LLM.

## Основная идея

Приложение получает актуальный прогноз погоды для конкретного географического положения пользователя через бесплатный API **Open-Meteo**.

Пользователь самостоятельно задаёт:

* географические координаты;
* период для прогноза;
* набор отображаемых погодных параметров.

На основании этих настроек приложение:

1. формирует запрос к Open-Meteo;
2. получает погодные данные для заданных координат;
3. нормализует ответ внешнего API;
4. формирует `WeatherSnapshot`;
5. определяет семантическое состояние погоды `WeatherCondition`;
6. формирует данные для отображения;
7. при необходимости передаёт нормализованные погодные данные локальной LLM **Ollama**;
8. Ollama формирует `WeatherPresentation`, выбирая только из заранее определённого набора визуальных ассетов;
9. приложение передаёт результат во frontend;
10. Vue.js отображает фактические погодные параметры, выбранные пользователем, текстовое AI-описание и соответствующие визуальные ассеты.

AI не является источником погодных данных.

AI используется как **AI Presentation Layer** — слой интерпретации и визуального представления уже полученных и нормализованных погодных данных.

---

# 2. Цели и ограничения

## Цель

* освоение полного цикла разработки;
* работа с внешним REST API;
* реализация кеширования внешних данных;
* использование Laravel Service Layer;
* использование Laravel Queue для асинхронных операций;
* работа с Vue.js;
* интеграция локальной LLM;
* создание портфолио-проекта.

## Ограничения

* использование бесплатного API погоды;
* минимальные инфраструктурные затраты;
* open-source инструменты;
* отсутствие постоянного фонового сбора погодных данных.

---

# 3. Стек технологий

## Backend

* Laravel 13;
* PostgreSQL;
* Laravel HTTP Client;
* Laravel Cache;
* Laravel Queue;
* Redis;
* Laravel Sanctum;
* Open-Meteo API.

## AI

* Laravel AI SDK — интеграция Laravel-приложения с AI;
* локальная LLM **Ollama**;
* Ollama является единственным AI/LLM-провайдером приложения;
* LLM используется в качестве AI Presentation Layer.

## Frontend

* Vue.js 3;
* Chart.js;
* TailwindCSS.

## Инфраструктура

* Docker;
* Laravel Sail;
* Ubuntu;
* Redis.

---

# 4. Архитектура

## Тип

**Модульный монолит.**

Все компоненты находятся в одном Laravel-приложении, но разделены по ответственности.

Основной принцип получения погодных данных: погода запрашивается по требованию пользователя (on-demand), исходя из его настроек.

Архитектура разделяет:

1. получение фактических погодных данных;
2. нормализацию погодных данных;
3. определение семантического состояния погоды;
4. AI-интерпретацию;
5. выбор визуального представления;
6. отображение данных во frontend.

Основной поток:

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
WeatherPresentation
      ↓
Predefined Assets
      ↓
Vue.js
```
---

# 5. Слои приложения

## 5.1 Presentation Layer

Отвечает за HTTP-входящие запросы и формирование API-ответов.

### Routes

`routes/api.php`

Отвечают за:

* authentication;
* получение настроек;
* сохранение настроек;
* получение погодных данных;
* получение Weather Presentation.

### Controllers

Например:

`app/Http/Controllers/Api/WeatherController.php`

Controller:

* принимает HTTP request;
* проверяет авторизацию;
* вызывает соответствующий Service;
* возвращает API Response.

Controller не содержит бизнес-логики.

### Requests

Например:

`app/Http/Requests/UpdateWeatherSettingsRequest.php`

Отвечает за:

* валидацию координат;
* валидацию количества дней;
* валидацию погодных параметров;
* валидацию настроек отображения.

---

# 5.2 Application Layer

Отвечает за orchestration бизнес-процессов.

## WeatherService

`app/Services/WeatherService.php`

Основная ответственность:

1. получение настроек пользователя;
2. формирование параметров запроса;
3. проверка Cache;
4. вызов OpenMeteoClient при отсутствии актуальных данных;
5. передача результата WeatherNormalizer;
6. формирование `WeatherSnapshot`;
7. определение `WeatherCondition`;
8. формирование DTO/структуры ответа для frontend;
9. постановка `GenerateWeatherPresentationJob` в очередь при необходимости.

`WeatherService` не отвечает за генерацию AI-текста непосредственно.

---

# 5.3 Infrastructure Layer

## OpenMeteoClient

`app/Infrastructure/Weather/OpenMeteoClient.php`

Использует Laravel HTTP Client для обращения к Open-Meteo.

Клиент отвечает только за:

* формирование HTTP-запроса;
* передачу latitude;
* передачу longitude;
* передачу forecast_period;
* передачу необходимых weather variables;
* обработку HTTP-ошибок;
* timeout;
* retry.

Приложение может запрашивать у Open-Meteo все необходимые погодные параметры независимо от того, какие параметры пользователь выбрал для отображения.

Выбор отображаемых параметров выполняется отдельно на уровне приложения.

---

# 6. WeatherNormalizer

`app/Services/Weather/WeatherNormalizer.php`

Отвечает за преобразование ответа Open-Meteo во внутренний формат приложения.

Задача:

```text
Open-Meteo JSON
      ↓
WeatherNormalizer
      ↓
WeatherSnapshot
```

Application-код не должен зависеть от структуры JSON конкретного внешнего API.

---

# 7. User Settings

Настройки пользователя хранятся в PostgreSQL.

## WeatherSettings

### 1. Таблица users

| Поле | Тип PostgreSQL | Назначение |
|---|---|---|
| `id` | `BIGINT` - PRIMARY KEY | Первичный ключ |
| `first_name` | `VARCHAR(255)` | Имя пользователя |
| `second_name` | `VARCHAR(255)` | Фамилия пользователя |
| `email` | `VARCHAR(255)` - UNIQUE | Email |
| `password` | `VARCHAR(255)` | Хеш пароля |
| `locale` | `VARCHAR(10)` | Язык интерфейса |
| `theme` | `VARCHAR(20)` | Тема интерфейса |
| `created_at` | `TIMESTAMP` | Дата создания |
| `updated_at` | `TIMESTAMP` | Дата изменения |

### 2. Таблица weather_settings

| Поле | Тип PostgreSQL | Назначение |
|---|---|---|
| `id` | `BIGINT` - PRIMARY KEY | Первичный ключ |
| `user_id` | `BIGINT` | FK → `users.id` |
| `latitude` | `DECIMAL(8,5)` | Широта |
| `longitude` | `DECIMAL(8,5)` | Долгота |
| `forecast_period` | `SMALLINT` | Количество дней прогноза |
| `temperature` | `BOOLEAN` | Отображать температуру |
| `apparent_temperature` | `BOOLEAN` | Отображать ощущаемую температуру |
| `relative_humidity` | `BOOLEAN` | Отображать относительную влажность |
| `precipitation` | `BOOLEAN` | Отображать осадки |
| `weather_code` | `BOOLEAN` | Отображать код погодного состояния |
| `cloud_cover` | `BOOLEAN` | Отображать облачность |
| `pressure` | `BOOLEAN` | Отображать атмосферное давление |
| `wind_speed` | `BOOLEAN` | Отображать скорость ветра |
| `wind_direction` | `BOOLEAN` | Отображать направление ветра |
| `wind_gusts` | `BOOLEAN` | Отображать порывы ветра |

`latitude` и `longitude` определяют географическую точку, для которой приложение получает прогноз.

Один пользователь имеет одну активную конфигурацию погоды.

### Принцип работы boolean-параметров

Boolean-поля `weather_settings` определяют **только параметры, отображаемые пользователю**.

Например:

```text
temperature = true
pressure = true
wind_speed = false
```

означает:

```text
Получить данные:
    temperature
    pressure
    wind_speed
    ...

Отобразить:
    temperature
    pressure

Не отображать:
    wind_speed
```

Приложение может получать все необходимые погодные параметры от Open-Meteo независимо от пользовательских настроек отображения.

Таким образом, настройки отображения не являются ограничением набора данных, получаемых от внешнего API.
---

# 8. Получение погодных данных

Получение погодных данных выполняется **on-demand**.

Основной сценарий:

```text
User authenticates
       ↓
Vue.js application loads
       ↓
GET /api/weather
       ↓
WeatherController
       ↓
WeatherService
       ↓
User WeatherSettings
       ↓
Cache lookup
       ↓
Cache HIT ──────────────┐
       │                │
       │ Cache MISS     │
       ▼                │
OpenMeteoClient         │
       │                │
       ▼                │
Open-Meteo API          │
       │                │
       ▼                │
WeatherNormalizer       │
       │                │
       ▼                │
WeatherSnapshot         │
       │                │
       ▼                │
WeatherCondition        │
       │                │
       └────────────────┘
                ↓
        Weather response
                ↓
              Vue.js
```
---

---

# 8.1. WeatherSnapshot

`WeatherSnapshot` — ключевая внутренняя сущность, представляющая нормализованное состояние погодных данных, полученных от Open-Meteo.

`WeatherSnapshot` является источником фактических метеорологических данных для последующей бизнес-логики и AI Presentation Layer.

Пример структуры:

```text
WeatherSnapshot
├── latitude
├── longitude
├── timestamp
├── temperature
├── apparent_temperature
├── relative_humidity
├── precipitation
├── weather_code
├── cloud_cover
├── pressure
├── wind_speed
├── wind_direction
└── wind_gusts
```

`WeatherSnapshot` не содержит AI-generated данных.

---

# 8.2. WeatherCondition

`WeatherCondition` представляет семантическое состояние погоды, определяемое приложением на основании `WeatherSnapshot`.

Для определения состояния используется детерминированная бизнес-логика.

Закрытый набор состояний:

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

Количество и значения состояний являются фиксированными на уровне приложения.

Основной поток:

```text
WeatherSnapshot
      ↓
WeatherConditionResolver
      ↓
WeatherCondition
```

`WeatherCondition` используется для:

* определения текущего семантического состояния погоды;
* выбора fallback-визуального представления;
* формирования контекста для Ollama;
* выбора соответствующих визуальных ассетов.

LLM не является источником `WeatherCondition`.

---

# 8.3. Season

Приложение использует закрытый набор из четырёх времён года:

```text
SPRING
SUMMER
AUTUMN
WINTER
```

`Season` определяется приложением на основании даты погодного прогноза.

`Season` используется совместно с `WeatherCondition` для определения допустимой иллюстрации `Landscape`.

Комбинация:

```text
Season + WeatherCondition
```

определяет конкретный вариант иллюстрации местности.

---

# 8.4. Asset System

ArtyWeather использует заранее подготовленный закрытый набор визуальных ассетов.

Основные категории:

```text
WeatherIcon
Landscape
```

Ассеты не генерируются во время работы приложения.

---

# 8.5. WeatherIcon

`WeatherIcon` представляет пиктограмму погодного состояния.

Предопределённый набор:

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

Каждому идентификатору соответствует заранее подготовленный графический asset.

Количество основных погодных пиктограмм:

```text
8
```

---

# 8.6. Landscape

`Landscape` представляет заранее подготовленную иллюстрацию одной и той же красочной живописной местности.

Основной принцип:

> Композиция местности остаётся одной и той же, а погодные условия и время года изменяются.

Количество вариантов:

```text
8 WeatherCondition × 4 Season = 32
```

Таким образом, для полного покрытия всех комбинаций необходимо подготовить **32 иллюстрации**.

Логический идентификатор может иметь вид:

```text
spring_clear
spring_rain
summer_clear
summer_rain
autumn_fog
winter_snow
```

Физический путь к asset не передаётся LLM.

---

# 8.7. WeatherPresentation

`WeatherPresentation` является результатом AI Presentation Layer.

Она объединяет фактическое семантическое состояние погоды с выбранным способом его визуального и текстового представления.

Структура:

```text
WeatherPresentation
├── weather_condition
├── season
├── weather_icon
├── landscape
├── summary
└── recommendation
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

`weather_icon` и `landscape` являются логическими идентификаторами заранее существующих ассетов.

---

# 8.8. Разделение фактической погоды и визуального представления

Архитектура должна явно разделять фактические погодные данные и их визуальное представление.

## Фактические данные

```text
Open-Meteo
      ↓
WeatherNormalizer
      ↓
WeatherSnapshot
      ↓
WeatherCondition
```

## Визуальное представление

```text
WeatherSnapshot
      +
WeatherCondition
      +
Season
      ↓
AI Presentation Layer
      ↓
WeatherPresentation
      ↓
WeatherIcon + Landscape + Summary + Recommendation
```

Изменение визуального представления не должно изменять фактические погодные данные.

---

# 9. Cache

Laravel Cache используется для уменьшения количества повторных запросов к Open-Meteo.

Кешируется результат запроса, сформированного на основании:

* latitude;
* longitude;
* forecast_period;
* других параметров, непосредственно влияющих на получаемые погодные данные.

Пользовательские boolean-параметры отображения **не должны входить в weather cache key**, поскольку они определяют только отображение уже полученных данных.

Пример логического ключа:

```text
weather:{latitude}:{longitude}:{forecast_period}
```

При наличии актуального кеша внешний API не вызывается.

При отсутствии кеша:

```text
Cache MISS
    ↓
Open-Meteo API
    ↓
WeatherNormalizer
    ↓
WeatherSnapshot
    ↓
Cache
    ↓
Application
```

Это позволяет нескольким пользователям использовать один и тот же актуальный результат для одинаковой географической точки и периода прогноза.

---

# 10. AI-интеграция

AI используется как **AI Presentation Layer**.

Основные задачи AI:

* генерация краткого summary;
* формирование рекомендации пользователю;
* выбор предопределённой пиктограммы погоды;
* выбор предопределённой иллюстрации Landscape;
* формирование итогового `WeatherPresentation`.

## AI Provider

Единственным AI/LLM-провайдером является **Ollama**.

Модель работает локально и вызывается приложением через Ollama API.

AI не получает необработанные данные непосредственно от внешнего API.

Структура:

```text
Open-Meteo
      ↓
WeatherNormalizer
      ↓
WeatherSnapshot
      ↓
WeatherCondition
      ↓
AiWeatherPresentationService
      ↓
Ollama
      ↓
WeatherPresentation
```

---

# 10.1. AiWeatherPresentationService

`app/Services/AI/AiWeatherPresentationService.php`

`AiWeatherPresentationService` является основным сервисом AI Presentation Layer.

Основная ответственность:

1. получить `WeatherSnapshot`;
2. получить `WeatherCondition`;
3. определить `Season`;
4. сформировать контекст для LLM;
5. сформировать AI prompt;
6. передать данные в Ollama;
7. получить structured output;
8. проверить результат;
9. проверить допустимость идентификаторов визуальных ассетов;
10. сформировать `WeatherPresentation`.

Сервис не отвечает за получение погодных данных от Open-Meteo.

---

# 10.2. Правила использования Ollama

### Правило 1. LLM не является источником погодных данных

**Ollama не является источником фактических погодных данных и не определяет произвольные погодные состояния.**

LLM работает только с данными, предварительно:

```text
полученными
    ↓
нормализованными
    ↓
проанализированными приложением
```

Фактические погодные данные поступают только из Open-Meteo.

---

### Правило 2. LLM не генерирует визуальные ассеты

LLM не генерирует:

* изображения;
* SVG;
* имена файлов;
* URL;
* пути к файлам;
* новые идентификаторы ассетов.

---

### Правило 3. LLM использует только закрытые наборы

LLM может выбирать только значения из заранее определённых наборов идентификаторов.

Для `WeatherIcon`:

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

Для `Landscape` допустимы только заранее существующие комбинации:

```text
{season}_{weather_condition}
```

Например:

```text
spring_clear
summer_rain
autumn_fog
winter_snow
```

Генерация новых идентификаторов не допускается.

---

# 10.3. Structured Output

Ollama должна возвращать структурированный результат.

Пример:

```json
{
    "weather_icon": "rain",
    "landscape": "autumn_rain",
    "summary": "Ожидается дождливая осенняя погода.",
    "recommendation": "Рекомендуется взять зонт."
}
```

Laravel валидирует полученный результат.

В случае недопустимого значения:

```text
LLM response
      ↓
Validation
      ↓
Invalid asset ID
      ↓
Fallback
```

Приложение не должно использовать непроверенный идентификатор ассета.

---

# 10.4. AI Presentation Flow

```text
WeatherSnapshot
      +
WeatherCondition
      +
Season
      ↓
AiWeatherPresentationService
      ↓
AI Prompt Builder
      ↓
Ollama
      ↓
Structured JSON
      ↓
Validation
      ↓
WeatherPresentation
```

---

# 11. GenerateWeatherPresentationJob

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
      ↓
Cache
```

`GenerateWeatherPresentationJob` отвечает за запуск AI Presentation pipeline.

Job не должен самостоятельно обращаться к Open-Meteo.

Необходимые погодные данные передаются в `AiWeatherPresentationService` в нормализованном виде.

Использование Queue позволяет не блокировать HTTP-запрос пользователя на время генерации AI-ответа.

---

# 12. Cache AI Weather Presentation

AI Presentation может сохраняться во временном кеше.

Ключ должен учитывать:

* координаты;
* период прогноза;
* hash нормализованных погодных данных;
* locale;
* версию набора ассетов или presentation schema при необходимости.

Пользовательские boolean-параметры отображения не должны влиять на AI Presentation cache, поскольку они определяют только то, какие части уже полученного результата отображаются во frontend.

Если исходные погодные данные и необходимые параметры контекста не изменились, повторный вызов Ollama не выполняется.

---

# 13. API

## Weather API

### `GET /api/weather`

Получение актуального прогноза для текущего пользователя.

Источник параметров:

* latitude;
* longitude;
* forecast_period.

Параметры берутся из `WeatherSettings`.

Приложение может получить полный набор необходимых погодных данных, после чего frontend отображает только те параметры, для которых соответствующее boolean-поле `WeatherSettings` установлено в `true`.

---

## `POST /api/weather/refresh`

Принудительное обновление погодных данных.

Используется, когда пользователь хочет получить максимально свежий результат, минуя обычный кеш.

Логика:

```text
POST /refresh
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

---

## `GET /api/weather/presentation`

Получение AI Weather Presentation.

Если presentation уже находится в кеше:

```text
Cache HIT
      ↓
WeatherPresentation
```

Если presentation генерируется:

```json
{
    "status": "processing"
}
```

После завершения Job frontend получает актуальный `WeatherPresentation` посредством повторного запроса.

Готовый ответ:

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

---

# 14. Authentication

Используется Laravel Sanctum.

Основной сценарий:

```text
Login
  ↓
Sanctum authentication
  ↓
Authenticated user
  ↓
Vue.js
  ↓
GET /api/weather
```

Получение погоды не является частью authentication logic.

Authentication только определяет текущего пользователя, после чего Weather API использует его `WeatherSettings`.

---

# 15. Frontend

Frontend реализуется на Vue.js 3.

Основной интерфейс приложения состоит из двух основных разделов:

* Weather Overview;
* Dashboard.

---

## 15.1. Application Header

Header отображается на основных страницах приложения.

Содержит:

* логотип ArtyWeather;
* название приложения;
* переключатель локали;
* переключатель темы;
* Logout.

Настройки локали и темы синхронизируются с пользовательскими настройками.

---

## 15.2. Weather Overview

Основной экран приложения для просмотра текущей погоды и прогноза.

Экран состоит из следующих визуальных блоков:

### Current Weather

Отображает:

* название географической точки;
* текущую температуру;
* WeatherCondition;
* WeatherIcon;
* выбранные пользователем погодные параметры.

Например:

* temperature;
* apparent temperature;
* relative humidity;
* precipitation;
* cloud cover;
* pressure;
* wind speed;
* wind direction;
* wind gusts.

Отображаются только те параметры, для которых соответствующее boolean-поле в `WeatherSettings` имеет значение `true`.

---

### Weather Landscape

Отображает `Landscape`, соответствующий:

* текущему `Season`;
* текущему `WeatherCondition`.

Landscape выбирается из заранее подготовленного набора визуальных ассетов.

Frontend получает логический идентификатор Landscape и сопоставляет его с соответствующим asset.

---

### Weather Forecast

Отображает прогноз погоды на выбранный пользователем `forecast_period`.

Для каждого временного периода могут отображаться:

* дата/время;
* WeatherIcon;
* WeatherCondition;
* погодные параметры, разрешённые пользователем.

---

### Weather Charts

Для временных рядов погодных данных используются графики.

Основные графики:

* Temperature Chart;
* Pressure Chart;
* Wind Chart;
* Precipitation Chart.

Конкретный график отображается только в случае наличия соответствующего погодного параметра в `WeatherSettings`.

Графики строятся на основании данных `WeatherSnapshot`.

---

### AI Weather Presentation

Отображает результат `WeatherPresentation`.

Содержит:

* WeatherIcon;
* Landscape;
* AI summary;
* AI recommendation.

AI-generated данные не заменяют фактические погодные данные.

---

## 15.3. Dashboard

Dashboard предназначен для настройки пользовательского представления погоды.

Пользователь может изменить:

* latitude;
* longitude;
* forecast period;
* отображаемые погодные параметры;
* locale;
* theme.

Каждый погодный параметр представлен boolean-настройкой.

Изменение настроек отображения не требует повторного получения погодных данных, если сами погодные данные остаются актуальными.

---

## 15.4. Application States

Frontend должен поддерживать следующие состояния:

* initial loading;
* weather loading;
* weather loaded;
* AI presentation processing;
* AI presentation ready;
* partial AI failure;
* Open-Meteo error;
* network error;
* invalid user settings;
* empty weather data.

При недоступности Ollama фактические погодные данные должны продолжать отображаться.

При недоступности AI Presentation Layer frontend должен использовать fallback presentation, если он предоставлен backend.

---

## 15.5. Responsive Layout

Frontend должен поддерживать:

* desktop;
* tablet;
* mobile.

Компоненты Weather Overview должны адаптироваться к ширине экрана.

Карточки погодных данных, графики и Landscape должны сохранять читаемость на мобильных устройствах.

---

## 15.6. Frontend Components

Основные Vue-компоненты:

```text
App
├── Header
│   ├── Logo
│   ├── LocaleSwitcher
│   ├── ThemeSwitcher
│   └── Logout
│
├── WeatherOverview
│   ├── CurrentWeather
│   ├── WeatherLandscape
│   ├── WeatherForecast
│   ├── TemperatureChart
│   ├── PressureChart
│   ├── WindChart
│   ├── PrecipitationChart
│   └── AiWeatherPresentation
│
└── Dashboard
    ├── LocationSettings
    ├── ForecastPeriodSettings
    ├── WeatherParametersSettings
    ├── LocaleSettings
    └── ThemeSettings
```

## 15.7. Chart.js

**Chart.js 4.x** используется для визуализации временных рядов погодных данных на странице Weather Overview.

Источник данных для графиков — нормализованные погодные данные `WeatherSnapshot`.

Основные графики:

* **Temperature Chart** — изменение температуры во времени;
* **Pressure Chart** — изменение атмосферного давления;
* **Wind Chart** — изменение скорости ветра;
* **Precipitation Chart** — изменение количества осадков.

Тип графика для временных рядов — `Line Chart`.

Графики должны:

* отображать данные за выбранный `forecast_period`;
* использовать временную шкалу по оси X;
* отображать единицы измерения соответствующего параметра;
* поддерживать responsive layout;
* корректно работать на desktop, tablet и mobile;
* поддерживать интерактивное отображение значения конкретной точки;
* использовать данные только из `WeatherSnapshot`;
* отображаться только для тех погодных параметров, которые пользователь разрешил в `WeatherSettings`.

Chart.js отвечает исключительно за визуализацию данных и не содержит бизнес-логики приложения.

Основная цепочка:

```text
WeatherSnapshot
      ↓
Weather Chart Data
      ↓
Vue.js Chart Component
      ↓
Chart.js
      ↓
Weather Chart
```

Конкретная конфигурация графиков и их визуальное оформление должны соответствовать общему дизайну ArtyWeather.

---

# 16. Полная цепочка получения погоды

```text
Vue.js
   ↓
GET /api/weather
   ↓
WeatherController
   ↓
WeatherService
   ↓
WeatherSettings
   ↓
Cache
   │
   ├── HIT ────────────────┐
   │                       │
   └── MISS                │
         ↓                 │
   OpenMeteoClient         │
         ↓                 │
   Open-Meteo API          │
         ↓                 │
   WeatherNormalizer       │
         ↓                 │
   WeatherSnapshot         │
         ↓                 │
   WeatherCondition        │
         ↓                 │
   Cache ──────────────────┤
                           ↓
                    WeatherResponse
                           ↓
                        Vue.js
```

---

# 17. Полная цепочка AI Presentation

```text
WeatherService
      ↓
WeatherSnapshot
      ↓
WeatherConditionResolver
      ↓
WeatherCondition
      ↓
Season
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
Structured JSON
      ↓
Validation
      ↓
WeatherPresentation
      ├── weather_condition
      ├── season
      ├── weather_icon
      ├── landscape
      ├── summary
      └── recommendation
      ↓
Cache
      ↓
Vue.js
      ↓
Visual Presentation
```

---

# 18. Обработка ошибок

Необходимо предусмотреть:

* timeout Open-Meteo;
* HTTP 4xx/5xx;
* недоступность Open-Meteo;
* некорректные координаты;
* превышение допустимого периода прогноза;
* недоступность Ollama;
* timeout AI;
* ошибки Queue;
* отсутствие настроек пользователя;
* некорректный structured output от Ollama;
* недопустимый идентификатор WeatherIcon;
* недопустимый идентификатор Landscape.

При временных ошибках внешнего API используются retry с ограниченным количеством попыток.

Если Open-Meteo временно недоступен и существует ещё актуальный кешированный результат, приложение может вернуть кешированные данные с соответствующим статусом.

Если Ollama недоступна, фактические погодные данные должны оставаться доступными.

При невозможности получить `WeatherPresentation` приложение должно использовать fallback, основанный на `WeatherCondition` и `Season`, без генерации новых ассетов.

---

# 19. Rate Limiting

Для API необходимо предусмотреть ограничение частоты запросов.

Особенно для:

* `/api/weather`;
* `/api/weather/refresh`;
* `/api/weather/presentation`.

Цель:

* предотвращение случайного DDoS самим frontend;
* защита Open-Meteo от чрезмерного количества запросов;
* предотвращение многократного запуска AI generation.

---

# 20. Нефункциональные требования

* Authentication через Sanctum;
* CSRF защита;
* валидация входящих данных;
* обработка ошибок внешнего API;
* Rate Limiting;
* Cache;
* Queue;
* Redis;
* локализация минимум на 2 языка;
* поддержка тёмной темы;
* retry для внешних запросов;
* retry для AI Job;
* graceful degradation при недоступности внешнего сервиса;
* отсутствие зависимости frontend от структуры Open-Meteo API;
* отсутствие зависимости frontend от структуры Ollama response;
* использование только предопределённых визуальных ассетов;
* запрет генерации изображений во время работы приложения;
* запрет генерации новых идентификаторов визуальных ассетов;
* разделение фактических погодных данных и AI Presentation;
* детерминированное определение `WeatherCondition`;
* валидация всех AI-generated asset identifiers.

---

# 21. План итераций разработки

## Итерация 1 — Подготовка окружения

* установка Laravel;
* настройка Sail;
* PostgreSQL;
* Redis;
* Sanctum;
* базовая авторизация;
* Git.

**Результат:** рабочий backend с авторизацией.

---

## Итерация 2 — Настройки пользователя

* WeatherSettings;
* координаты;
* период прогноза;
* погодные параметры отображения;
* API настроек;
* Dashboard.

**Результат:** пользователь может настроить координаты, период прогноза и параметры отображаемой погоды.

---

## Итерация 3 — Интеграция Open-Meteo

* OpenMeteoClient;
* WeatherService;
* WeatherNormalizer;
* WeatherSnapshot;
* WeatherConditionResolver;
* WeatherCondition;
* HTTP error handling;
* retry;
* получение прогноза по координатам.

**Результат:** приложение получает и нормализует актуальный прогноз для конкретного пользователя и определяет его семантическое погодное состояние.

---

## Итерация 4 — Cache

* настройка Cache;
* формирование weather cache key;
* TTL;
* Cache HIT/MISS;
* принудительное обновление;
* защита от повторных запросов.

**Результат:** минимизация запросов к Open-Meteo.

---

## Итерация 5 — Frontend

* Vue.js 3;
* Weather page;
* Dashboard;
* Chart.js;
* графики температуры;
* графики давления;
* графики ветра;
* отображение прогноза;
* отображение только выбранных пользователем погодных параметров;
* интеграция WeatherIcon;
* интеграция Landscape.

**Результат:** пользовательский интерфейс приложения с визуальным представлением погоды.

---

## Итерация 6 — AI Presentation Layer

* интеграция Ollama;
* AI prompt builder;
* AiWeatherPresentationService;
* GenerateWeatherPresentationJob;
* Redis Queue;
* Queue Worker;
* structured output;
* валидация AI response;
* WeatherPresentation;
* WeatherIcon;
* Landscape;
* набор предопределённых визуальных ассетов;
* AI summary;
* AI recommendation;
* AI Presentation cache;
* fallback presentation;
* отображение результата во frontend.

**Результат:** локальная LLM формирует текстовое описание и рекомендацию, а также выбирает только из заранее определённого набора визуальных ассетов для представления погодного состояния.

---

## Итерация 7 — Полировка

* локализация;
* тёмная тема;
* Rate Limiting;
* оптимизация Cache;
* обработка ошибок;
* тестирование WeatherConditionResolver;
* тестирование WeatherPresentation;
* тестирование AI structured output;
* тестирование допустимости asset identifiers;
* тестирование fallback;
* README;
* документация архитектуры;
* screenshots проекта.

**Результат:** готовый PET-проект для портфолио.

---

# 22. Итоговая архитектура

Итоговая архитектура ArtyWeather:

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
                           Cache
                              ↓
                           Vue.js
                              ↓
                    Visual Presentation
```

## Основной принцип архитектуры

```text
                    FACTUAL WEATHER
                          │
                          ↓
                     Open-Meteo
                          │
                          ↓
                  WeatherSnapshot
                          │
                          ↓
                  WeatherCondition
                          │
                          ↓
                 AI PRESENTATION LAYER
                          │
                          ↓
                       Ollama
                          │
                          ↓
                  WeatherPresentation
                          │
              ┌───────────┼───────────┐
              ↓           ↓           ↓
        WeatherIcon   Landscape     Text
              │           │           │
              └───────────┼───────────┘
                          ↓
                       Vue.js
```

Ключевое архитектурное правило:

> **Open-Meteo является источником фактических погодных данных. `WeatherSnapshot` представляет нормализованные факты. `WeatherCondition` представляет детерминированное семантическое состояние погоды. Ollama работает только как AI Presentation Layer и не изменяет фактические данные. `WeatherPresentation` определяет, как эти данные будут представлены пользователю. Визуальные ассеты являются заранее подготовленными и принадлежат закрытому набору приложения.**

LLM может выбирать только существующие значения из этого набора. Генерация новых идентификаторов, имён файлов, URL или изображений запрещена.
