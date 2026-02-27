Technical specifications - project ArtyWeather
======================================

# 1. Общая идея проекта
ArtyWeather — веб-приложение (PET-проект) для получения практического опыта разработки.

## Основная идея:
* Получение погодных данных через бесплатный API (Open-Meteo)
* Сохранение данных в БД
* Отображение: графиков давления, трендов, простых карт с циклонами и антициклонами
* Генерация текстового summary через ИИ (описание погоды, рекомендации по одежде)

## Проект должен демонстрировать:
* работу с внешним API
* нормализацию данных
* очереди и scheduler
* визуализацию данных
* интеграцию LLM

# 2. Цели и ограничения
## Цель:
* Освоение полного цикла разработки
* Создание портфолио-проекта (GitHub repository)

## Ограничения:
* Использование бесплатных API
* Минимальные инфраструктурные затраты
* Open-source инструменты

# 3. Стек технологий
## Backend:
* Laravel 11
* cron (через Laravel Scheduler)
* PostgreSQL,
* Open-Meteo API
* API от OpenAI (модель уровня GPT-4o-mini или аналог)

## Frontend:
* Vue.js 3
* Chart.js
* TailwindCSS

## Инфраструктура:
* Docker (через Laravel Sail)
* Ubuntu

# 4. Архитектура

## Тип: Модульный монолит
(всё в одном приложении Laravel, но разделено по ответственности)

## Слои приложения:

### 1. Presentation Layer (отвечает за HTTP-вход и отдачу ответа)

#### Используемые сущности Laravel:
* Routes (routes/api.php)
Роль: Точка входа, Привязка URL - Controller
* Controllers (app/Http/Controllers/Api/WeatherController.php)
Роль: Не содержит бизнес-логики, только orchestration, делегирует в Service или Job.
* Requests (app/Http/Requests/UpdateSettingsRequest.php)
Роль: валидация

### 2. Application Layer (оркестрация процессов)

#### Используемые сущности Laravel:
* Services (app/Services/WeatherService.php)
Роль: получение данных, вызов нормализации, сохранение, вызов анализа.
* Jobs (app/Jobs/CollectWeatherJob.php), сущности: ShouldQueue, Dispatchable, Queueable, Redis driver.
Роль: Асинхронная обработка, Изоляция долгих операций

### 3. Domain Layer (чистая логика без Laravel-зависимостей)
PressureAnalyzer(app/Domain/Weather/PressureAnalyzer.php)

### 4. Infrastructure Layer (всё, что связано с внешним миром)
   * HTTP Client (Illuminate\Support\Facades\Http, app/Infrastructure/Weather/OpenMeteoClient.php)
   * AI Service (app/Infrastructure/AI/AiWeatherSummaryService.php)

### 5. Presentation Layer
   * Models (Eloquent), app/Models/WeatherSnapshot.php
   * Migrations, database/migrations

### 6. Scheduler (routes/console.php),
   Laravel сущности: Schedule, schedule:run, cron (вне Laravel).

## Основные компоненты:

* HTTP Client → запрос к Open-Meteo
* WeatherNormalizer → нормализация ответа API
* WeatherService → бизнес-логика
* PressureAnalyzer → определение циклон / антициклон
* AiWeatherSummaryService → генерация текста
* Scheduler → периодический сбор данных
* Queue → асинхронная обработка AI

## Полная схема взаимодействия:
cron ->
schedule:run ->
CollectWeatherJob ->
WeatherService ->
(OpenMeteoClient → External API) ->
WeatherNormalizer ->
PressureAnalyzer ->
WeatherSnapshot::create() ->
GenerateSummaryJob ->
(AiWeatherSummaryService → OpenAI) ->
weather_summaries

# 5. Модель данных (таблицы)

## Users:
* id;
* name;
* email;
* password;
* locale;
* theme.

## Weather_snapshots:
* id;
* user_id;
* latitude;
* longitude;
* pressure;
* wind_speed;
* temperature;
* collected_at;
* system_type (cyclone / anticyclone / neutral).

## weather_summaries:
* id;
* snapshot_id;
* summary_text;
* created_at.

# 6. Роли и права доступа

Роли не предусмотрены.
Есть только User, которому доступен:
* просмотр данных;
* настройка параметров;
* запуск обновления данных.

# 7. API

## Weather API (Open-Meteo)
User может настраивать:
* координаты;
* частоту обновления;
* параметры запроса (давление, ветер, температура).

## AI API (OpenAI)
Назначение:
* генерация краткого summary;
* рекомендации по погоде.

# 8. Логика бизнес-процессов

# Основные процессы:

## 1. Настройка:
   * выбор координат;
   * настройка частоты обновления;
   * включение/отключение AI summary;
   * выбор локали.

## 2. Сбор данных:
   * Scheduler запускает сбор;
   * Данные сохраняются;
   * Анализируется давление;
   * Определяется тип системы.

## 3. Генерация AI summary:
   * Запускается Job;
   * Отправляется агрегированная информация;
   * Сохраняется текст.

## 4. Просмотр:
   User видит:
   * график давления;
   * текущий тип системы;
   * карту;
   * текст от AI;

# 9. Фронтенд

## Login ->

## Header top menu (закрепленное вверху  справа меню):
* Кнопка Log out (выход из авторизации)
* Locale (переключатель локали)
* Theme toggle (переключатель темы)

## Header picture (тематический рисунок на весь хедер)

## Header bottom menu (между Header picture и основным окном приложения):
* Вкладка Weather (открываеться по дефолту, отображение настроенных через Dashboard данных, графики Chart.js? , текстовое summary от ИИ)
* Dashboard (настройка запросов данных и параметров).

## Основное окно приложения (отображение вкладок Weather и Dashboard)

## Footer

# 10. Нефункциональные требования:

* Авторизация через Sanctum
* CSRF защита
* Валидация входящих данных
* Обработка ошибок API
* Локализация (минимум 2 языка)
* Поддержка темной темы
* Retry для AI-запросов

# 11. План итераций разработки

## Итерация 1 — Подготовка окружения

* Установка Laravel
* Настройка Sail (Docker)
* Подключение PostgreSQL
* Настройка Sanctum
* Базовая авторизация
* Настройка Git
### Результат: Рабочий backend с авторизацией.

## Итерация 2 — Интеграция Weather API:

* Реализация OpenMeteoClient
* Создание WeatherService
* Создание миграций
* Сохранение snapshot
### Результат: Данные сохраняются в БД

## Итерация 3 — Анализ давления

* Реализация PressureAnalyzer
* Определение типа системы
* Добавление поля system_type
* Unit-тесты
### Результат: Определяется cyclone / anticyclone.

## Итерация 4 — Scheduler и очереди

* Настройка schedule:run
* Реализация CollectWeatherJob
* Настройка Redis
* Логирование ошибок
### Результат: Автоматический сбор данных.

## Итерация 5 — Frontend базовый

* Установка Vue 3
* Настройка роутинга
* Реализация страницы Weather
* Подключение Chart.js
* Отображение графика давления
### Результат: Видимый график данных.

## Итерация 6 — Карта

* Подключение Leaflet
* Отображение координат
* Маркер cyclone / anticyclone
* Popup с информацией
### Результат: Простейшая карта с системами давления.

## Итерация 7 — AI-интеграция

* Реализация AiWeatherSummaryService
* Создание Job
* Отправка агрегированных данных в OpenAI
* Сохранение summary
* Отображение в интерфейсе
### Результат: AI генерирует текст.

## Итерация 8 — Полировка

* Локализация
* Темная тема
* Оптимизация запросов
* Обработка ошибок
* Документация README
* Скриншоты проекта
### Результат: Готовый PET-проект для портфолио.