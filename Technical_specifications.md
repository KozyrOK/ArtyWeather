Technical specifications - project ArtyWeather
======================================

# 1. Общая идея проекта
**ArtyWeather** — веб-приложение (PET-проект) для анализа погодных данных и их осмысленного отображения с использованием AI.

## Основная идея:
* Получение погодных данных через Open-Meteo
* Сохранение данных в БД
* Генерация AI-интерпретации полученных данных
* Визуализация: графиков давления, текущих погодных данных, карты местности

## Проект должен демонстрировать:
* работу с внешним API
* архитектуру Laravel-приложения
* очереди и scheduler
* визуализацию данных
* интеграцию AI через Laravel AI SDK

# 2. Цели и ограничения
## Цель:
* Освоение стека технологий
* Работа с AI SDK
* Создание портфолио-проекта

## Ограничения:
* Бесплатные API
* Минимальная инфраструктура

# 3. Стек технологий
## Backend:
* Laravel 13
* Laravel Scheduler
* Laravel Queue (Redis)
* PostgreSQL / MySQL
* Open-Meteo API
* Laravel AI SDK

## Frontend:
* Vue.js 3
* Inertia.js
* Chart.js
* Leaflet
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
PressureAnalyzer

### 4. Infrastructure Layer
   * OpenMeteoClient
   * AI SDK

### 5.Persistence Layer
   * Models (Eloquent), app/Models/WeatherSnapshot.php
   * Migrations, database/migrations

## Полная схема взаимодействия:
Scheduler ->
CollectWeatherJob ->
WeatherService ->
OpenMeteoClient ->
WeatherNormalizer ->
PressureAnalyzer ->
GenerateInsightJob ->
WeatherInsightService ->
Laravel AI SDK ->
AI Provider ->
weather_summaries

# 5. Модель данных (таблицы)

## Users:
* id;
* name;
* email;
* password;
* latitude;
* longitude;
* locale;
* theme.

## Weather_snapshots:
* id;
* user_id;
* latitude;
* longitude;
* temperature;
* pressure;
* wind_speed;
* collected_at;
* system_type (cyclone / anticyclone / neutral).

## weather_summaries:
* id;
* snapshot_id;
* summary_text;
* clothing_advice;
* risk_level;
* system_explanation;
* provider;
* model;
* created_at.

# 6. Роли и права доступа

Роли не предусмотрены.
Есть только User, которому доступен:
* просмотр данных;
* настройка параметров;
* запуск обновления данных.

# 7. API

## GET /api/weather
текущие данные
## GET /api/weather/history
история
## POST /api/weather/refresh
запуск обновления

# 8. Бизнес-логика

# Основные процессы:

## 1. Настройка:
   * выбор координат;
   * настройка частоты обновления;
   * включение/отключение AI summary;
   * выбор локали.

## 2. Сбор данных:
   * Scheduler запускает Job;
   * Данные сохраняются;

## 3. Генерация AI summary:
   * Запускается Job;
   * PressureAnalyzer определяет: cyclone, anticyclone, neutral;
   * Отправляется агрегированная информация;

## 4. Просмотр:
   User видит:
   * график давления;
   * текущий тип системы;
   * карту;
   * текст от AI;

# 9. Фронтенд

## Login ->

## логотип ArtyWeather

## Header top menu (закрепленное вверху  справа меню):
* Кнопка Log out (выход из авторизации)
* Locale (переключатель локали)
* Theme toggle (переключатель темы)

## Header picture (тематический рисунок на весь хедер)

## Header bottom menu (между Header picture и основным окном приложения):

### Вкладка Weather: 
* Блок 1 — Current Weather (город, температура, давление, ветер)
* Блок 2 — Pressure Chart (график давления)
* Блок 3 — System (cyclone / anticyclone, визуальный индикатор)
* Блок 4 — AI Summary (summary, clothing advice, risk level)
* Блок 5 — Map (одна точка (пользователь), тип системы)

### Вкладка Dashboard (координаты, частота обновления, включение AI).

## Основное окно приложения (отображение вкладок Weather и Dashboard)

## Footer

# 10. Нефункциональные требования:

* Sanctum auth
* CSRF
* Валидация входящих данных
* Обработка ошибок API
* Retry для AI
* Логирование
* Кеширование
* Возможность смены AI provider
* Локализация (минимум 2 языка)
* Поддержка темной темы

# 11. План итераций разработки

## Итерация 1 — Подготовка окружения

* Установка Laravel 13 + Sail
* Подключение PostgreSQL
* Настройка Sanctum

### Результат: Рабочий backend с авторизацией.

## Итерация 2 — Интеграция Weather API:

* OpenMeteoClient
* Создание WeatherService
* Создание миграций
* Сохранение snapshot
### Результат: Данные сохраняются в БД

## Итерация 3 — Анализ давления

* Реализация PressureAnalyzer
* Определение типа системы
* Добавление поля system_type

### Результат: Определяется типы систем

## Итерация 4 — Scheduler и очереди

* Настройка schedule:run
* Реализация CollectWeatherJob
* Настройка Redis

### Результат: Автоматический сбор данных

## Итерация 5 — Frontend базовый

* Установка Vue + Inertia
* Настройка роутинга
* Реализация страницы Weather
* Подключение Chart.js
* Отображение графика давления
### Результат: базовый UI

## Итерация 6 — Карта

* Подключение Leaflet
* Отображение координат
* Маркер cyclone / anticyclone

### Результат: визуализация систем

## Итерация 7 — AI SDK

* Установка Laravel AI SDK
* Настройка provider
* WeatherInsightService
* GenerateInsightJob
* structured output
### Результат: AI работает

## Итерация 8 — AI UX

* clothing advice
* risk level
* отображение карточек

### Результат: полноценный AI-блок

## Итерация 9 — Полировка

* Локализация
* Темная тема
* Оптимизация запросов
* Обработка ошибок
* Документация README
* Скриншоты проекта
### Результат: Готовый PET-проект