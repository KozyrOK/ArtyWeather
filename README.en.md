<p align="center">
  <strong>🇬🇧 English</strong> |
  <a href="./README.ru.md">🇷🇺 Русский</a>
</p>

---

# ArtyWeather

**ArtyWeather** is a Laravel-based PET project for obtaining practical experience with modern web application development, external REST APIs, caching, asynchronous processing, Vue.js, Chart.js, and local LLM integration.

The application retrieves weather forecasts for a user-defined geographic location through the free **Open-Meteo API** and presents the normalized weather data through a Vue.js interface.

A local **Ollama** LLM is used only as an **AI Presentation Layer**. It does not provide weather data and does not replace the deterministic application logic used to determine weather conditions.

## Project Goals

- Practice modern Laravel application architecture
- Work with an external REST API
- Implement a modular monolith
- Apply a Service Layer and infrastructure abstractions
- Normalize external API responses into application DTO/domain structures
- Implement Laravel Cache and Redis
- Implement asynchronous processing with Laravel Queue
- Build a Vue.js 3 frontend
- Visualize weather time series with Chart.js
- Integrate a local LLM through Ollama
- Implement structured AI output and strict validation
- Practice graceful degradation when external services are unavailable
- Build a portfolio-ready PET project

## Core Architecture Principle

ArtyWeather explicitly separates **factual weather data** from **AI-generated presentation**.

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

The key rule is:

> **Open-Meteo is the source of factual weather data. `WeatherSnapshot` represents normalized facts. `WeatherCondition` is determined by deterministic application logic. Ollama is used only as an AI Presentation Layer and must not modify factual weather data.**

## Tech Stack

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
- Local LLM

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

## Domain / Application Concepts

### User Settings

Each user has one active weather configuration stored in PostgreSQL.

The configuration contains:

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

The boolean weather settings determine **what is displayed to the user**, not which data is requested from Open-Meteo.

For example:

```text
temperature = true
pressure = true
wind_speed = false
```

means that the application may retrieve all required weather data, while the frontend displays temperature and pressure but not wind speed.

### WeatherSnapshot

`WeatherSnapshot` is the normalized internal representation of factual weather data.

It contains values such as:

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

`WeatherSnapshot` contains no AI-generated data.

### WeatherCondition

`WeatherCondition` is a deterministic semantic classification calculated by the application.

The closed set is:

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

The LLM is not responsible for determining this value.

### Season

The application uses a closed set of seasons:

```text
SPRING
SUMMER
AUTUMN
WINTER
```

The season is determined by the application from the forecast date.

### Visual Assets

ArtyWeather uses predefined visual assets. Assets are never generated at runtime.

There are two main categories:

```text
WeatherIcon
Landscape
```

#### WeatherIcon

There are 8 predefined weather icons:

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

The application uses illustrations of the same scenic landscape. The composition remains consistent while the season and weather conditions change.

The complete set contains:

```text
8 WeatherCondition × 4 Season = 32 illustrations
```

Logical identifiers follow the pattern:

```text
{season}_{weather_condition}
```

Examples:

```text
spring_clear
summer_rain
autumn_fog
winter_snow
```

The physical asset path is never generated or returned by the LLM.

## AI Presentation Layer

Ollama is the **only AI/LLM provider** used by the application.

The AI layer receives already processed application data:

```text
WeatherSnapshot
    +
WeatherCondition
    +
Season
```

and produces a validated `WeatherPresentation`.

### WeatherPresentation

The presentation contains:

```text
weather_condition
season
weather_icon
landscape
summary
recommendation
```

Example:

```json
{
  "weather_condition": "RAIN",
  "season": "AUTUMN",
  "weather_icon": "rain",
  "landscape": "autumn_rain",
  "summary": "Rainy autumn weather is expected.",
  "recommendation": "It is recommended to take an umbrella."
}
```

The AI may select only existing asset identifiers.

It must not:

- generate images
- generate SVG
- generate filenames
- generate URLs
- generate filesystem paths
- invent new asset identifiers
- act as a weather-data source
- modify factual weather values

All AI structured output is validated by Laravel before it is used.

## Backend Architecture

The project follows a **modular monolith** architecture.

### Presentation Layer

Responsible for HTTP requests and API responses.

Examples:

```text
routes/api.php
app/Http/Controllers/Api/
app/Http/Requests/
```

Controllers must not contain business logic.

### Application Layer

The main orchestration service is:

```text
app/Services/WeatherService.php
```

It is responsible for:

1. obtaining user settings;
2. building weather request parameters;
3. checking cache;
4. calling the Open-Meteo client when required;
5. normalizing the response;
6. producing `WeatherSnapshot`;
7. determining `WeatherCondition`;
8. preparing the response for the frontend;
9. dispatching AI presentation generation when required.

### Infrastructure Layer

Open-Meteo access is isolated in:

```text
app/Infrastructure/Weather/OpenMeteoClient.php
```

The client is responsible for:

- HTTP request construction
- coordinates
- forecast period
- weather variables
- timeout handling
- retry handling
- HTTP error handling

Application code must not depend directly on the Open-Meteo JSON structure.

### WeatherNormalizer

```text
app/Services/Weather/WeatherNormalizer.php
```

Transforms:

```text
Open-Meteo JSON
    ↓
WeatherSnapshot
```

This keeps the application independent from the external provider's response format.

### AI Service

```text
app/Services/AI/AiWeatherPresentationService.php
```

Responsibilities include:

- building the LLM context
- building the AI prompt
- calling Ollama through the Laravel AI integration
- requesting structured output
- validating the result
- validating asset identifiers
- producing `WeatherPresentation`

The service does not retrieve weather data from Open-Meteo.

## Caching

Laravel Cache is used to reduce repeated requests to Open-Meteo.

The weather cache key is based on data that affects the actual weather request, such as:

```text
weather:{latitude}:{longitude}:{forecast_period}
```

User display booleans must **not** be part of the weather cache key because they affect presentation only.

This also allows different users requesting the same location and forecast period to reuse an appropriate cached weather result.

AI Presentation uses a separate cache. Its key may include:

- coordinates
- forecast period
- a hash of normalized weather data
- locale
- presentation/asset schema version when required

If the relevant weather data and presentation context have not changed, the application should avoid unnecessary calls to Ollama.

## Asynchronous AI Processing

AI Presentation generation is performed asynchronously.

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

The job must not call Open-Meteo directly.

The normalized weather data required by the AI service is passed through the application layer.

This prevents AI generation from blocking the main weather request.

## API

### `GET /api/weather`

Returns the current weather forecast for the authenticated user's configured location.

The location and forecast period are taken from `WeatherSettings`.

The application may return the complete normalized weather dataset, while the frontend displays only the parameters enabled by the user's settings.

### `POST /api/weather/refresh`

Forces a weather refresh.

Logical flow:

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

Returns the AI Presentation state.

While generation is running:

```json
{
  "status": "processing"
}
```

When ready:

```json
{
  "status": "ready",
  "presentation": {
    "weather_condition": "RAIN",
    "season": "AUTUMN",
    "weather_icon": "rain",
    "landscape": "autumn_rain",
    "summary": "Rainy autumn weather is expected.",
    "recommendation": "It is recommended to take an umbrella."
  }
}
```

## Authentication

Authentication is implemented with Laravel Sanctum.

The weather API uses the authenticated user only to determine whose `WeatherSettings` should be used.

Weather retrieval itself remains separate from authentication logic.

## Frontend

The frontend is implemented with Vue.js 3.

The main application areas are:

- **Weather Overview**
- **Dashboard**

### Weather Overview

The main weather screen contains:

- current weather
- geographic location
- current temperature
- WeatherCondition
- WeatherIcon
- selected weather parameters
- Weather Landscape
- forecast
- weather charts
- AI Weather Presentation

Only weather parameters enabled in `WeatherSettings` are displayed.

### Weather Charts

Chart.js 4.x is used for weather time-series visualization.

Main charts:

- Temperature Chart
- Pressure Chart
- Wind Chart
- Precipitation Chart

The charts:

- use normalized weather data
- cover the selected forecast period
- use a time scale
- display appropriate measurement units
- support responsive layouts
- provide interactive point values
- are shown only when the corresponding weather parameter is enabled
- contain no business logic

### Dashboard

The Dashboard allows the user to configure:

- latitude
- longitude
- forecast period
- displayed weather parameters
- locale
- theme

Changing display preferences should not trigger a new weather request when the underlying weather data is still valid.

### Application States

The frontend supports:

- initial loading
- weather loading
- weather loaded
- AI presentation processing
- AI presentation ready
- partial AI failure
- Open-Meteo error
- network error
- invalid user settings
- empty weather data

If Ollama is unavailable, factual weather data must remain available.

If the AI layer fails, the backend may provide a fallback presentation based on `WeatherCondition` and `Season`.

### Responsive Design

The interface supports:

- desktop
- tablet
- mobile

Weather cards, charts, and landscape illustrations must remain readable and usable on smaller screens.

## Error Handling and Resilience

The application handles:

- Open-Meteo timeouts
- Open-Meteo HTTP 4xx/5xx responses
- Open-Meteo unavailability
- invalid coordinates
- invalid forecast periods
- Ollama unavailability
- AI timeouts
- queue failures
- missing user settings
- invalid Ollama structured output
- invalid WeatherIcon identifiers
- invalid Landscape identifiers

External API requests use a limited retry strategy.

If Open-Meteo is temporarily unavailable and an appropriate cached result exists, the application may return cached weather data with an appropriate status.

If Ollama is unavailable, factual weather data must remain usable.

No runtime image generation is allowed.

## Rate Limiting

Rate limiting should be applied to the main weather endpoints, especially:

```text
/api/weather
/api/weather/refresh
/api/weather/presentation
```

The purpose is to prevent accidental excessive requests, protect the external weather API, and avoid repeated AI generation.

## Localization and Theme

The application supports at least two interface languages.

The application also supports a dark theme.

Locale and theme are part of user settings and are synchronized with the frontend.

## Development Roadmap

### Iteration 1 — Environment

- Laravel 13
- Laravel Sail
- PostgreSQL
- Redis
- Sanctum
- basic authentication
- Git

**Result:** working authenticated backend environment.

### Iteration 2 — User Settings

- WeatherSettings
- coordinates
- forecast period
- display parameters
- settings API
- Dashboard

**Result:** users can configure their weather view.

### Iteration 3 — Open-Meteo Integration

- OpenMeteoClient
- WeatherService
- WeatherNormalizer
- WeatherSnapshot
- WeatherConditionResolver
- WeatherCondition
- HTTP error handling
- retry
- forecast retrieval

**Result:** normalized weather data and deterministic weather conditions.

### Iteration 4 — Cache

- Laravel Cache
- weather cache key
- TTL
- cache hit/miss
- forced refresh
- duplicate-request protection

**Result:** reduced external API usage.

### Iteration 5 — Frontend

- Vue.js 3
- Weather Overview
- Dashboard
- Chart.js
- temperature chart
- pressure chart
- wind chart
- precipitation chart
- forecast visualization
- selected weather parameters
- WeatherIcon
- Landscape

**Result:** complete weather visualization interface.

### Iteration 6 — AI Presentation Layer

- Ollama integration
- AI prompt builder
- AiWeatherPresentationService
- GenerateWeatherPresentationJob
- Redis Queue
- Queue Worker
- structured output
- AI response validation
- WeatherPresentation
- predefined visual assets
- AI summary
- AI recommendation
- AI Presentation cache
- fallback presentation
- frontend integration

**Result:** local LLM-powered presentation of already processed weather data.

### Iteration 7 — Polish

- localization
- dark theme
- rate limiting
- cache optimization
- error handling
- WeatherConditionResolver tests
- WeatherPresentation tests
- structured output tests
- asset identifier validation tests
- fallback tests
- documentation
- screenshots

**Result:** portfolio-ready PET project.

## Deployment

ArtyWeather is designed to run in a containerized development environment using Docker and Laravel Sail.

### Requirements

- Docker
- Docker Compose
- Git
- Ollama for the AI Presentation Layer

### Environment

Create the application environment file from the project example:

```bash
cp .env.example .env
```

Configure the required application, PostgreSQL, Redis, and Ollama settings in `.env`.

The Ollama model is managed locally by the Ollama installation and is not stored in the repository.

### Start the Application

Start the Sail environment:

```bash
./vendor/bin/sail up -d
```

Install PHP dependencies:

```bash
./vendor/bin/sail composer install
```

Install frontend dependencies:

```bash
./vendor/bin/sail npm install
```

Generate the application key:

```bash
./vendor/bin/sail artisan key:generate
```

Run migrations:

```bash
./vendor/bin/sail artisan migrate
```

Start the frontend development server:

```bash
./vendor/bin/sail npm run dev
```

Build the frontend for production:

```bash
./vendor/bin/sail npm run build
```

### Redis Queue Worker

AI Presentation generation requires a running queue worker.

For local development:

```bash
./vendor/bin/sail artisan queue:work
```

### Ollama

Install and manage Ollama separately from the application.

Verify the installation:

```bash
ollama --version
```

Verify available models:

```bash
ollama list
```

The exact model is intentionally not fixed by this README because the technical specification defines **Ollama as the only LLM provider**, while the concrete local model can be configured for the development environment.

When Laravel runs inside Docker and Ollama runs on the host machine, configure the application so the container can reach the host's Ollama API.

## Useful Commands

Stop the environment:

```bash
./vendor/bin/sail down
```

Restart the environment:

```bash
./vendor/bin/sail up -d
```

View application logs:

```bash
./vendor/bin/sail logs
```

Access the application container:

```bash
./vendor/bin/sail shell
```

Run migrations:

```bash
./vendor/bin/sail artisan migrate
```

Run tests:

```bash
./vendor/bin/sail artisan test
```

Run the queue worker:

```bash
./vendor/bin/sail artisan queue:work
```

## Architecture Summary

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

## Key Design Rules

1. **Open-Meteo is the source of factual weather data.**
2. **WeatherSnapshot contains normalized factual data only.**
3. **WeatherCondition is determined deterministically by the application.**
4. **Ollama is the only LLM provider.**
5. **Ollama is an AI Presentation Layer, not a weather-data source.**
6. **AI receives normalized application data, not raw external API data.**
7. **AI may select only predefined WeatherIcon and Landscape identifiers.**
8. **AI-generated asset identifiers must always be validated.**
9. **No images or new visual assets are generated at runtime.**
10. **User display booleans affect presentation, not the weather cache key.**
11. **AI failures must not make factual weather data unavailable.**
12. **Frontend components must not depend on the raw Open-Meteo or Ollama response structures.**
