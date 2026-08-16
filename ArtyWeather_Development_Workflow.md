# ArtyWeather — Development Workflow and Codex Prompts

Этот файл объединяет:

- рабочий workflow разработчика;
- команды для подготовки и проверки среды;
- порядок выполнения семи итераций;
- готовые промты для Codex на каждую итерацию.

## Источники требований

Главный источник функциональных и архитектурных требований:

```text
Technical_specifications.md
```

Этот файл не заменяет техническое задание. Он определяет **порядок практической разработки** и взаимодействия с Codex.

При обнаружении противоречия между этим файлом и `Technical_specifications.md` приоритет имеет `Technical_specifications.md`. Если противоречие нельзя однозначно разрешить по ТЗ, вопрос необходимо отдельно уточнить в рамках проекта, а не самостоятельно менять архитектуру.

---

# 1. Модель работы

В проекте используются две разные зоны ответственности.

## Разработчик

Разработчик самостоятельно:

- подготавливает локальное окружение;
- устанавливает зависимости;
- создаёт и изменяет файлы;
- выполняет команды;
- настраивает Docker/Sail;
- запускает PostgreSQL и Redis;
- запускает Queue Worker;
- устанавливает и запускает Ollama;
- запускает Laravel и Vue;
- выполняет миграции;
- запускает тесты;
- проверяет frontend;
- сообщает Codex результат и ошибки.

## Codex

Codex используется как:

- генератор кода;
- помощник по реализации текущей итерации;
- генератор содержимого файлов;
- помощник по исправлению конкретных ошибок.

### Важное правило

**Codex не является исполнителем локальной инфраструктуры и не является источником пошаговых инструкций для подготовки среды.**

Команды подготовки среды и порядок действий разработчика определены в этом файле.

При запросе текущей итерации у Codex нужно просить прежде всего:

1. изучить `Technical_specifications.md`;
2. изучить текущие файлы проекта;
3. дать код необходимых файлов;
4. дать точные изменения существующих файлов;
5. дать необходимые тесты.

Не просить Codex самостоятельно составлять план установки Docker, PostgreSQL, Redis, Ollama и т. п., если это уже описано здесь.

---

# 2. Общий цикл каждой итерации

Для каждой итерации используется один и тот же цикл:

```text
1. Подготовить среду по этому файлу
             ↓
2. Дать Codex промт текущей итерации
             ↓
3. Получить код
             ↓
4. Внести код в проект
             ↓
5. Выполнить миграции / build / tests
             ↓
6. Исправить ошибки через Codex
             ↓
7. Проверить критерии завершения
             ↓
8. Перейти к следующей итерации
```

Не следует сразу реализовывать все семь итераций.

---

# 3. Общий шаблон запроса Codex

Перед каждой итерацией используется соответствующий промт из разделов ниже.

Базовый принцип:

```text
Изучи Technical_specifications.md.
Изучи текущую структуру проекта.
Сейчас работаем только над Iteration N.
Предоставь код необходимых файлов.
Не переходи к следующей итерации.
```

Codex должен работать с **реальным текущим состоянием репозитория**, а не с предположением о том, какие файлы уже существуют.

Если файл уже существует — Codex должен показать точные изменения для него.

Если файл новый — Codex должен дать его полное содержимое.

---

# 4. Важные обозначения команд

## HOST

Команды, выполняемые непосредственно на Ubuntu:

```bash
docker ...
docker compose ...
php ...
composer ...
node ...
npm ...
ollama ...
git ...
```

## Laravel/Sail container

Команды приложения:

```bash
./vendor/bin/sail artisan ...
./vendor/bin/sail composer ...
./vendor/bin/sail npm ...
./vendor/bin/sail php ...
```

## Не путать HOST и container

Например:

```bash
ollama list
```

выполняется на HOST.

А:

```bash
./vendor/bin/sail artisan migrate
```

выполняется через Laravel Sail.

---

# 5. Начальная подготовка проекта

## 5.1 Проверить HOST

В корне репозитория:

```bash
pwd
git status
git branch --show-current

docker --version
docker compose version

php --version
composer --version

node --version
npm --version

laravel --version
```

Если `laravel --version` сообщает, что команда отсутствует, не нужно отдельно устанавливать Laravel Installer только ради этого.

Для существующего репозитория способ создания Laravel 13 определяется исходным состоянием проекта.

---

## 5.2 Проверить содержимое репозитория

```bash
find . -maxdepth 2 -type f | sort
```

Проверить assets:

```bash
find assets -type f | sort
```

Проверить Git:

```bash
git status --short
```

На этом этапе должны быть сохранены:

```text
Technical_specifications.md
README.md
README.en.md
assets/
```

Не удалять и не перезаписывать их при создании Laravel skeleton.

---

# 6. ITERATION 1 — Environment and Foundation

## Цель

Получить:

```text
Laravel 13
Docker
Laravel Sail
PostgreSQL
Redis
Sanctum
basic authentication
```

Weather-функциональность пока не реализуется.

---

## 6.1 Создание Laravel 13

Поскольку исходный репозиторий уже содержит Markdown-файлы и `assets/`, не выполнять без проверки:

```bash
laravel new .
```

и не выполнять без проверки:

```bash
composer create-project laravel/laravel . "^13.0"
```

Это может перезаписать существующие файлы.

Безопасный вариант — создать Laravel skeleton во временной директории:

```bash
cd ..
composer create-project laravel/laravel artyweather-laravel "^13.0"

cd artyweather-laravel

php artisan --version
```

После этого необходимо перенести Laravel skeleton в исходный репозиторий, сохранив существующие:

```text
Technical_specifications.md
README.md
README.en.md
assets/
```

Перед переносом проверить:

```bash
find . -maxdepth 2 -type f | sort
```

После переноса снова:

```bash
git status
```

---

## 6.2 Git checkpoint

После успешного создания Laravel:

```bash
git status
```

Проверить, что `.env` не будет добавлен.

Затем:

```bash
git add .
git commit -m "chore: initialize Laravel 13 application"
```

---

## 6.3 Laravel Sail

Sail должен содержать как минимум:

```text
Laravel application
PostgreSQL
Redis
```

После создания/настройки:

```bash
./vendor/bin/sail up -d
```

Проверить:

```bash
./vendor/bin/sail ps
```

и:

```bash
docker compose ps
```

Проверить конфигурацию:

```bash
docker compose config
```

---

## 6.4 Проверить Laravel внутри container

```bash
./vendor/bin/sail artisan --version
./vendor/bin/sail php --version
./vendor/bin/sail composer --version
./vendor/bin/sail node --version
./vendor/bin/sail npm --version
```

---

## 6.5 PostgreSQL

После определения фактического имени PostgreSQL service в `docker-compose.yml` настроить `.env`.

Ожидаемая структура:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=artyweather
DB_USERNAME=sail
DB_PASSWORD=password
```

Значения должны соответствовать фактической Sail-конфигурации.

Проверить:

```bash
./vendor/bin/sail artisan migrate
```

и:

```bash
./vendor/bin/sail artisan migrate:status
```

---

## 6.6 Redis

Проверить Laravel configuration:

```bash
./vendor/bin/sail artisan about
```

Убедиться, что Redis service работает:

```bash
./vendor/bin/sail ps
```

Не устанавливать дополнительный Redis client без необходимости.

---

## 6.7 Sanctum

После того как структура Laravel API определена:

```bash
./vendor/bin/sail artisan install:api
```

Если текущая версия проекта уже содержит API/Sanctum или Codex предлагает другой актуальный способ, не выполнять команду повторно.

После установки:

```bash
./vendor/bin/sail artisan migrate
```

---

## 6.8 Authentication

Реализовать authentication согласно ТЗ и коду, предоставленному Codex.

Проверить:

```bash
./vendor/bin/sail artisan route:list
```

и:

```bash
./vendor/bin/sail artisan test
```

---

## 6.9 Prompt для Codex — Iteration 1

```text
Изучи Technical_specifications.md и текущее состояние репозитория.

Сейчас работаем только над Iteration 1 — Environment and Foundation.

Моя локальная среда и инфраструктура подготавливаются мной самостоятельно согласно ArtyWeather_Development_Workflow.md. Не составляй для меня инструкции по установке Docker, PostgreSQL, Redis или Ollama.

Твоя задача на этой итерации — предложить код приложения.

Определи, какие Laravel-файлы необходимо создать или изменить для:
- Laravel 13 foundation;
- PostgreSQL configuration;
- Redis configuration;
- Sanctum/API authentication;
- базовой authentication;
- необходимых configuration-файлов.

Для каждого нового файла дай полное содержимое.
Для существующего файла дай точные изменения.
Не оставляй псевдокод или многоточия вместо кода.

Сначала кратко перечисли файлы, которые будут затронуты.
Затем дай полный код.

Не реализуй WeatherSettings, Open-Meteo, Cache, Vue.js, Chart.js, Queue или Ollama.

Не переходи к Iteration 2.
```

---

## 6.10 Iteration 1 verification

```bash
./vendor/bin/sail ps
./vendor/bin/sail artisan --version
./vendor/bin/sail artisan migrate:status
./vendor/bin/sail artisan route:list
./vendor/bin/sail artisan test
```

### Готово, если:

```text
[ ] Laravel 13
[ ] Sail работает
[ ] PostgreSQL работает
[ ] Redis работает
[ ] Sanctum работает
[ ] authentication работает
[ ] migrations проходят
[ ] tests проходят
```

---

# 7. ITERATION 2 — WeatherSettings

## Цель

Создать пользовательские настройки:

```text
users
weather_settings
```

В соответствии с `Technical_specifications.md`.

Основные поля `weather_settings`:

```text
user_id
latitude
longitude
forecast_period
temperature
apparent_temperature
relative_humidity
precipitation
weather_code
cloud_cover
pressure
wind_speed
wind_direction
wind_gusts
```

Также учитываются:

```text
locale
theme
AI summary/presentation setting
```

если они предусмотрены актуальной версией ТЗ.

---

## 7.1 Создать migration

После получения кода от Codex:

```bash
./vendor/bin/sail artisan make:migration create_weather_settings_table
```

Внести предложенный Codex код.

Затем:

```bash
./vendor/bin/sail artisan migrate
```

Проверить:

```bash
./vendor/bin/sail artisan migrate:status
```

---

## 7.2 Model

Создать:

```bash
./vendor/bin/sail artisan make:model WeatherSetting
```

После внесения кода проверить:

```bash
./vendor/bin/sail artisan test
```

---

## 7.3 API

Codex должен предоставить конкретные Controller/Request/Resource/Response классы.

После внесения:

```bash
./vendor/bin/sail artisan route:list --path=api
```

---

## 7.4 Проверка базы

При необходимости в локальной разработке можно использовать:

```bash
./vendor/bin/sail artisan migrate:fresh
```

**Внимание:** команда удаляет текущую development database.

После неё:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
```

---

## 7.5 Prompt для Codex — Iteration 2

```text
Изучи Technical_specifications.md и текущее состояние проекта.

Сейчас работаем только над Iteration 2 — User Weather Settings.

Моя подготовка среды и выполнение migrations выполняются мной самостоятельно.

Твоя задача — только предложить код приложения.

Реализуй:
- изменения users, если они требуются;
- WeatherSetting model;
- migration weather_settings;
- User ↔ WeatherSetting relationship;
- casts;
- validation;
- API для чтения и изменения WeatherSettings;
- необходимые Controllers;
- Form Requests;
- Resources/DTO/Responses, если они предусмотрены архитектурой;
- tests.

Учитывай, что boolean weather settings определяют только отображение данных и не должны определять набор данных, который Open-Meteo должен возвращать.

Для каждого нового файла дай полный код.
Для существующего файла дай точные изменения.
Не давай мне инструкции по установке инфраструктуры.

Не реализуй Open-Meteo, Cache, Vue.js, Chart.js, Queue или Ollama.

Не переходи к Iteration 3.
```

---

## 7.6 Verification

```bash
./vendor/bin/sail artisan migrate:status
./vendor/bin/sail artisan route:list --path=api
./vendor/bin/sail artisan test
```

### Готово, если:

```text
[ ] WeatherSettings существует
[ ] User relationship работает
[ ] validation работает
[ ] API работает
[ ] одна конфигурация пользователя поддерживается
[ ] tests проходят
```

---

# 8. ITERATION 3 — Open-Meteo and Weather Domain

## Цель

Реализовать:

```text
OpenMeteoClient
WeatherNormalizer
WeatherSnapshot
WeatherConditionResolver
WeatherCondition
WeatherService
WeatherController
```

Основная цепочка:

```text
WeatherController
      ↓
WeatherService
      ↓
OpenMeteoClient
      ↓
Open-Meteo
      ↓
WeatherNormalizer
      ↓
WeatherSnapshot
      ↓
WeatherConditionResolver
      ↓
WeatherCondition
```

---

## 8.1 OpenMeteoClient

Файл согласно ТЗ:

```text
app/Infrastructure/Weather/OpenMeteoClient.php
```

Клиент отвечает за:

- HTTP request;
- latitude;
- longitude;
- forecast period;
- weather variables;
- timeout;
- retry;
- HTTP errors.

Application code не должен парсить Open-Meteo JSON напрямую.

---

## 8.2 WeatherNormalizer

Файл:

```text
app/Services/Weather/WeatherNormalizer.php
```

Цепочка:

```text
Open-Meteo JSON
      ↓
WeatherNormalizer
      ↓
WeatherSnapshot
```

---

## 8.3 WeatherCondition

Закрытый набор:

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

Определяется только детерминированной логикой приложения.

---

## 8.4 API

Основные endpoint:

```text
GET /api/weather
POST /api/weather/refresh
```

---

## 8.5 Prompt для Codex — Iteration 3

```text
Изучи Technical_specifications.md и текущее состояние проекта.

Сейчас работаем только над Iteration 3 — Open-Meteo and Weather Domain.

Моя инфраструктура уже подготовлена. Твоя задача — только предложить код приложения.

Реализуй:
- OpenMeteoClient;
- WeatherNormalizer;
- WeatherSnapshot;
- WeatherCondition;
- WeatherConditionResolver;
- WeatherService;
- WeatherController;
- необходимые Requests/Resources/DTO;
- GET /api/weather;
- POST /api/weather/refresh;
- timeout;
- bounded retry;
- HTTP error handling;
- tests.

OpenMeteoClient должен находиться в Infrastructure Layer.
WeatherNormalizer должен изолировать приложение от raw Open-Meteo JSON.
WeatherSnapshot должен содержать только фактические погодные данные.
WeatherCondition должен определяться детерминированно приложением.

Не добавляй Cache, Queue, Ollama, AI Presentation или Vue.js.

Для каждого нового файла дай полный код.
Для существующего файла дай точные изменения.
Не давай инструкции по подготовке Docker/PostgreSQL/Redis.

Не переходи к Iteration 4.
```

---

## 8.6 Verification

```bash
./vendor/bin/sail artisan route:list --path=api
./vendor/bin/sail artisan test
```

Проверить вручную authenticated API:

```text
GET /api/weather
POST /api/weather/refresh
```

Порт брать из фактической Sail/Vite configuration.

### Готово, если:

```text
[ ] Open-Meteo request works
[ ] normalization works
[ ] WeatherSnapshot works
[ ] WeatherCondition deterministic
[ ] GET /api/weather
[ ] POST /api/weather/refresh
[ ] timeout/retry
[ ] HTTP error handling
[ ] tests
```

---

# 9. ITERATION 4 — Weather Cache

## Цель

Кешировать нормализованные погодные данные и минимизировать повторные обращения к Open-Meteo.

Логическая схема:

```text
weather:{latitude}:{longitude}:{forecast_period}
```

Boolean display settings в ключ не входят.

---

## 9.1 Cache

Codex должен реализовать cache logic в application layer.

Проверять:

```text
Cache MISS
    ↓
Open-Meteo
    ↓
WeatherNormalizer
    ↓
WeatherSnapshot
    ↓
Cache
```

и:

```text
Cache HIT
    ↓
WeatherSnapshot
```

---

## 9.2 Refresh

`POST /api/weather/refresh` должен корректно работать с weather cache согласно ТЗ.

---

## 9.3 Prompt для Codex — Iteration 4

```text
Изучи Technical_specifications.md и текущее состояние проекта.

Сейчас работаем только над Iteration 4 — Weather Cache.

Твоя задача — только предложить код приложения.

Реализуй:
- weather cache;
- cache key generation;
- TTL;
- Cache HIT/MISS;
- использование cached WeatherSnapshot;
- refresh/invalidation для /api/weather/refresh;
- необходимые изменения WeatherService;
- tests.

Логический cache key:
weather:{latitude}:{longitude}:{forecast_period}

Boolean settings отображения пользователя не должны входить в weather cache key.

Кешировать нужно результат, пригодный для повторного использования application layer, а не raw Open-Meteo response, если это соответствует архитектуре ТЗ.

Не изменяй архитектуру OpenMeteoClient/WeatherNormalizer без необходимости.

Не реализуй Vue.js, Chart.js, Queue или Ollama.

Для каждого нового файла дай полный код.
Для существующего файла дай точные изменения.

Не давай инфраструктурные инструкции.
Не переходи к Iteration 5.
```

---

## 9.4 Verification

```bash
./vendor/bin/sail artisan test
```

Проверить:

```text
Первый запрос → Cache MISS → Open-Meteo
Повторный запрос → Cache HIT → Open-Meteo не вызывается
Refresh → cache становится неактуальным согласно реализации
```

### Готово, если:

```text
[ ] cache работает
[ ] key работает
[ ] TTL работает
[ ] HIT работает
[ ] MISS работает
[ ] refresh работает
[ ] display settings не влияют на weather cache key
[ ] tests проходят
```

---

# 10. ITERATION 5 — Vue.js / Chart.js / Frontend

## Цель

Создать frontend:

```text
Vue.js 3
Chart.js 4.x
TailwindCSS
```

---

## 10.1 Перед началом — inventory assets

Это выполняется разработчиком, а не Codex.

```bash
find assets -type f | sort
```

Для определения типов:

```bash
find assets -type f -print0 | xargs -0 file
```

Составить inventory:

```text
WeatherIcon:
...

Landscape:
...

Logo:
...

Favicon:
...
```

### Жёсткое правило

Использовать только существующие assets.

Запрещено:

- генерировать новые изображения;
- скачивать заменяющие изображения;
- создавать новые WeatherIcon;
- создавать новые Landscape;
- создавать SVG-заглушки;
- заменять существующие assets.

Физическое расположение файлов можно изменить.

---

## 10.2 Проверить package.json

```bash
cat package.json
```

и:

```bash
./vendor/bin/sail npm list --depth=0
```

Не устанавливать пакет, который уже есть.

Если Vue/Chart.js отсутствуют, выполнить команды, которые соответствуют коду и package manager проекта.

После установки:

```bash
./vendor/bin/sail npm list --depth=0
```

---

## 10.3 Frontend development

```bash
./vendor/bin/sail npm run dev
```

Vite port брать из фактического `vite.config.*`/`package.json`, а не предполагать.

---

## 10.4 Production build

```bash
./vendor/bin/sail npm run build
```

---

## 10.5 Prompt для Codex — Iteration 5

```text
Изучи Technical_specifications.md и текущее состояние проекта.

Сейчас работаем только над Iteration 5 — Frontend.

Твоя задача — только предложить код frontend/backend integration.

Реализуй:
- Vue.js 3 application;
- API client/composables/services;
- Weather Overview;
- Dashboard;
- WeatherForecast;
- CurrentWeather;
- WeatherLandscape;
- WeatherIcon integration;
- Logo integration;
- LocaleSwitcher;
- ThemeSwitcher;
- Logout;
- TemperatureChart;
- PressureChart;
- WindChart;
- PrecipitationChart;
- loading/error/application states;
- responsive layout;
- необходимые backend/frontend API changes.

Chart.js должен быть версии 4.x.

Charts:
- Line Chart;
- time scale;
- selected forecast_period;
- correct units;
- responsive;
- interactive point values;
- data only from WeatherSnapshot-derived API data;
- shown only when corresponding WeatherSettings boolean is enabled;
- no business logic inside chart components.

Assets уже существуют в репозитории. Не создавай и не предлагай новые изображения.

Используй фактические имена файлов из assets, если они доступны в репозитории. Если mapping необходимо реализовать, сделай его через application asset mapping, а не через filesystem paths generated by AI.

Для каждого нового файла дай полный код.
Для существующего файла дай точные изменения.

Не реализуй Ollama, Queue или AI Presentation.

Не давай мне инструкции по установке Docker или инфраструктуры.

Не переходи к Iteration 6.
```

---

## 10.6 Verification

```bash
./vendor/bin/sail npm run build
./vendor/bin/sail artisan test
```

Проверить браузером:

```text
Weather Overview
Dashboard
WeatherIcon
Landscape
WeatherForecast
Temperature Chart
Pressure Chart
Wind Chart
Precipitation Chart
```

Проверить состояния:

```text
initial loading
weather loading
weather loaded
Open-Meteo error
network error
invalid settings
empty weather data
```

### Готово, если:

```text
[ ] Vue.js 3
[ ] Dashboard
[ ] Weather Overview
[ ] existing logo
[ ] existing favicon
[ ] existing WeatherIcon
[ ] existing Landscape
[ ] 4 Chart.js charts
[ ] responsive
[ ] frontend build
[ ] API integration
```

---

# 11. ITERATION 6 — Ollama / AI Presentation Layer

## Цель

Добавить:

```text
Laravel AI SDK
Ollama
GenerateWeatherPresentationJob
Redis Queue
AiWeatherPresentationService
WeatherPresentation
structured output
validation
fallback
AI cache
```

---

## 11.1 Ollama на HOST

Проверить:

```bash
ollama --version
ollama list
```

Если Ollama отсутствует — установить отдельно на HOST согласно актуальной официальной инструкции.

---

## 11.2 Model

Выбор модели необходимо сделать до интеграции.

Проверить:

```bash
ollama list
```

После выбора:

```bash
ollama pull <MODEL_NAME>
```

Проверить:

```bash
ollama run <MODEL_NAME>
```

Не подключать Laravel к Ollama, пока Ollama сама не отвечает.

---

## 11.3 Docker → HOST Ollama

Внутри Laravel container:

```text
localhost
```

не следует автоматически считать адресом HOST.

Нужно использовать адрес/hostname, соответствующий Docker configuration.

Проверка connectivity выполняется после получения кода/configuration от Codex.

---

## 11.4 Laravel AI SDK

Проверить:

```bash
./vendor/bin/sail composer show
```

После этого установить необходимый пакет Laravel AI SDK согласно текущей версии Laravel и требованиям ТЗ.

Не устанавливать альтернативные AI providers.

Единственный AI provider:

```text
Ollama
```

---

## 11.5 Queue Worker

После реализации Job:

```bash
./vendor/bin/sail artisan queue:work
```

Worker должен работать во время ручной проверки AI.

---

## 11.6 AI Presentation

Вход:

```text
WeatherSnapshot
WeatherCondition
Season
```

Выход:

```text
weather_condition
season
weather_icon
landscape
summary
recommendation
```

AI не должен:

```text
generate image
generate SVG
generate filename
generate URL
generate filesystem path
invent asset ID
modify factual weather
```

---

## 11.7 Prompt для Codex — Iteration 6

```text
Изучи Technical_specifications.md и текущее состояние проекта.

Сейчас работаем только над Iteration 6 — AI Presentation Layer.

Твоя задача — только предложить код приложения.

Реализуй:
- Laravel AI SDK integration;
- Ollama provider configuration;
- AI prompt/context builder;
- AiWeatherPresentationService;
- GenerateWeatherPresentationJob;
- Redis Queue integration;
- WeatherPresentation;
- structured output;
- validation;
- asset identifier validation;
- AI presentation cache;
- fallback presentation;
- GET /api/weather/presentation;
- необходимые frontend integration changes;
- tests.

Input AI layer:
WeatherSnapshot
WeatherCondition
Season

Output:
weather_condition
season
weather_icon
landscape
summary
recommendation

Критические правила:
- Ollama не является источником погодных данных;
- Ollama не определяет WeatherCondition;
- Ollama не изменяет factual weather data;
- Ollama не генерирует изображения;
- Ollama не генерирует SVG;
- Ollama не генерирует filenames;
- Ollama не генерирует URLs;
- Ollama не генерирует filesystem paths;
- Ollama не создаёт новые asset identifiers;
- weather_icon и landscape должны быть ограничены закрытым набором;
- structured output должен валидироваться Laravel;
- при ошибке Ollama фактические погодные данные должны оставаться доступными;
- fallback должен использовать WeatherCondition + Season и существующие assets.

Для каждого нового файла дай полный код.
Для существующего файла дай точные изменения.
Не давай мне инструкции по установке Docker/Redis/Ollama: инфраструктуру я подготавливаю отдельно по этому workflow.

Не переходи к Iteration 7.
```

---

## 11.8 Verification

Backend:

```bash
./vendor/bin/sail artisan test
```

Queue:

```bash
./vendor/bin/sail artisan queue:work
```

Ollama HOST:

```bash
ollama list
```

Проверить API:

```text
GET /api/weather/presentation
```

Состояния:

```text
processing
ready
fallback
partial AI failure
```

Negative tests:

```text
invalid weather_condition
invalid season
invalid weather_icon
invalid landscape
missing field
wrong field type
malformed structured output
Ollama unavailable
AI timeout
Queue failure
```

### Готово, если:

```text
[ ] Ollama работает
[ ] Laravel AI SDK работает
[ ] Docker → Ollama works
[ ] Queue works
[ ] Job works
[ ] WeatherPresentation works
[ ] structured output validated
[ ] asset IDs validated
[ ] AI cache works
[ ] fallback works
[ ] weather remains available when AI fails
[ ] tests pass
```

---

# 12. ITERATION 7 — Polish and Final Verification

## Цель

Подготовить PET-проект к финальному состоянию.

---

## 12.1 Backend tests

```bash
./vendor/bin/sail artisan test
```

---

## 12.2 Routes

```bash
./vendor/bin/sail artisan route:list
```

---

## 12.3 Migrations

```bash
./vendor/bin/sail artisan migrate:status
```

---

## 12.4 Frontend

```bash
./vendor/bin/sail npm run build
```

---

## 12.5 Docker

```bash
./vendor/bin/sail ps
docker compose ps
```

---

## 12.6 Ollama HOST

```bash
ollama list
```

---

## 12.7 Assets

```bash
find assets -type f | sort
```

Если assets были перемещены, проверять уже их фактический application location.

Проверить:

```text
[ ] logo
[ ] favicon
[ ] WeatherIcon
[ ] Landscape
[ ] no replacement images
[ ] no generated runtime images
```

---

## 12.8 Git

```bash
git status
git diff
```

Проверить:

```text
.env не отслеживается
секреты не попали в Git
generated/cache files не добавлены
assets сохранены
tests сохранены
documentation сохранена
```

---

## 12.9 Prompt для Codex — Iteration 7

```text
Изучи Technical_specifications.md и текущее состояние проекта.

Сейчас работаем только над Iteration 7 — Polish and Final Verification.

Твоя задача — только предложить кодовые изменения и тесты.

Проверь и при необходимости реализуй:
- localization;
- dark theme;
- rate limiting;
- cache optimization;
- Open-Meteo error handling;
- Ollama error handling;
- Queue retry handling;
- WeatherConditionResolver tests;
- WeatherPresentation tests;
- structured output tests;
- asset identifier validation tests;
- fallback tests;
- API tests;
- необходимые frontend fixes;
- README/documentation changes, если они относятся к коду проекта.

Не переписывай рабочую архитектуру без необходимости.
Не добавляй новые зависимости без явной необходимости.
Не создавай новые визуальные assets.

Для каждого нового файла дай полный код.
Для существующего файла дай точные изменения.

Не давай мне инструкции по Docker/Ollama/инфраструктуре.
Не переходи к новой итерации — это финальная итерация.
```

---

# 13. Финальная проверка проекта

## Backend

```bash
./vendor/bin/sail artisan test
```

## Frontend

```bash
./vendor/bin/sail npm run build
```

## Laravel

```bash
./vendor/bin/sail artisan about
./vendor/bin/sail artisan route:list
./vendor/bin/sail artisan migrate:status
```

## Infrastructure

```bash
./vendor/bin/sail ps
docker compose ps
```

## Ollama

HOST:

```bash
ollama list
```

## Assets

```bash
find assets -type f | sort
```

---

# 14. Финальный end-to-end flow

## Weather

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
   ├── HIT ─────────────────┐
   │                        │
   └── MISS                  │
         ↓                  │
   OpenMeteoClient          │
         ↓                  │
   Open-Meteo               │
         ↓                  │
   WeatherNormalizer        │
         ↓                  │
   WeatherSnapshot          │
         ↓                  │
   WeatherCondition         │
         ↓                  │
   Cache ───────────────────┤
                            ↓
                     WeatherResponse
                            ↓
                         Vue.js
```

## AI Presentation

```text
WeatherService
      ↓
WeatherSnapshot
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
      ↓
AI Presentation Cache
      ↓
Vue.js
```

---

# 15. Финальные архитектурные правила

1. Open-Meteo — единственный источник фактических погодных данных.
2. `WeatherSnapshot` содержит только нормализованные фактические данные.
3. `WeatherCondition` определяется детерминированно приложением.
4. Ollama — единственный LLM provider.
5. Ollama является AI Presentation Layer.
6. AI не заменяет factual weather data.
7. AI не получает raw Open-Meteo response.
8. AI не генерирует изображения.
9. AI не генерирует filesystem paths, URLs или filenames.
10. AI не создаёт новые asset identifiers.
11. Используются только существующие визуальные assets проекта.
12. `WeatherIcon` и `Landscape` выбираются только из закрытого набора.
13. Все AI structured outputs валидируются.
14. Weather cache и AI presentation cache разделены.
15. Display booleans не входят в weather cache key.
16. AI generation выполняется через Queue.
17. Недоступность Ollama не должна скрывать фактические погодные данные.
18. Frontend не зависит от raw Open-Meteo/Ollama response.
19. Chart.js отвечает только за визуализацию.
20. Business logic не должна находиться внутри chart components.
21. Runtime generation визуальных assets запрещена.
22. Новые зависимости добавляются только при необходимости.
23. Каждая итерация проверяется до перехода к следующей.
24. При архитектурном противоречии приоритет имеет `Technical_specifications.md`.
