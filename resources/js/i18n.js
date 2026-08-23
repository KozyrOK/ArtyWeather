const messages = {
    en: {
        appName: 'ArtyWeather',
        appDescription: 'Weather, interpreted beautifully.',

        welcomeTitle: 'Welcome to ArtyWeather',
        welcomeText:
            'Get a clear weather forecast for your location with a clean interface and intelligent presentation.',
        login: 'Login',
        logout: 'Logout',

        email: 'Email',
        password: 'Password',
        signIn: 'Sign in',
        signingIn: 'Signing in…',
        invalidCredentials: 'The email or password is incorrect.',
        loginRequired: 'Please sign in to continue.',

        dashboard: 'Dashboard',
        weatherOverview: 'Weather overview',

        loadingWeather: 'Loading weather…',
        refreshWeather: 'Refresh weather',
        emptyWeather: 'No weather data available.',

        latitude: 'Latitude',
        longitude: 'Longitude',
        forecastDays: 'Forecast days',

        locale: 'Language',
        theme: 'Theme',
        light: 'Light',
        dark: 'Dark',

        weatherParameters: 'Weather parameters',
        saveSettings: 'Save settings',
        saving: 'Saving…',
        settingsLoading: 'Settings are loading…',

        aiPresentation: 'AI Presentation',
        ready: 'Ready',
        processing: 'Processing',
        loadingPresentation: 'Loading presentation…',
        presentationFallback:
            'AI presentation is being generated. Actual weather data remains available.',

        currentWeather: 'Current weather',
        forecast: 'Forecast',
        charts: 'Charts',
        temperature: 'Temperature',
        apparentTemperature: 'Feels like',
        relativeHumidity: 'Humidity',
        precipitation: 'Precipitation',
        weatherCode: 'Weather code',
        cloudCover: 'Cloud cover',
        pressure: 'Pressure',
        windSpeed: 'Wind speed',
        windDirection: 'Wind direction',
        windGusts: 'Wind gusts',

        weatherCondition: 'Weather condition',
        coordinates: 'Coordinates',
        recommendation: 'Recommendation',
        summary: 'Summary',

        clear: 'Clear',
        partlyCloudy: 'Partly cloudy',
        cloudy: 'Cloudy',
        rain: 'Rain',
        heavyRain: 'Heavy rain',
        snow: 'Snow',
        fog: 'Fog',
        storm: 'Storm',

        requestFailed: 'Request failed.',
    },

    ru: {
        appName: 'ArtyWeather',
        appDescription: 'Погода, представленная красиво.',

        welcomeTitle: 'Добро пожаловать в ArtyWeather',
        welcomeText:
            'Получайте точный прогноз погоды для выбранного местоположения в удобном интерфейсе с интеллектуальным представлением данных.',
        login: 'Войти',
        logout: 'Выйти',

        email: 'Email',
        password: 'Пароль',
        signIn: 'Войти',
        signingIn: 'Выполняется вход…',
        invalidCredentials: 'Неверный email или пароль.',
        loginRequired: 'Для продолжения необходимо войти.',

        dashboard: 'Настройки',
        weatherOverview: 'Обзор погоды',

        loadingWeather: 'Загружаем погоду…',
        refreshWeather: 'Обновить погоду',
        emptyWeather: 'Нет данных о погоде.',

        latitude: 'Широта',
        longitude: 'Долгота',
        forecastDays: 'Дней прогноза',

        locale: 'Язык',
        theme: 'Тема',
        light: 'Светлая',
        dark: 'Тёмная',

        weatherParameters: 'Погодные параметры',
        saveSettings: 'Сохранить настройки',
        saving: 'Сохраняем…',
        settingsLoading: 'Настройки загружаются…',

        aiPresentation: 'AI-представление',
        ready: 'Готово',
        processing: 'Генерируется',
        loadingPresentation: 'Загружаем представление…',
        presentationFallback:
            'AI-представление генерируется. Фактические погодные данные доступны.',

        currentWeather: 'Текущая погода',
        forecast: 'Прогноз',
        charts: 'Графики',
        temperature: 'Температура',
        apparentTemperature: 'Ощущается как',
        relativeHumidity: 'Влажность',
        precipitation: 'Осадки',
        weatherCode: 'Код погоды',
        cloudCover: 'Облачность',
        pressure: 'Давление',
        windSpeed: 'Скорость ветра',
        windDirection: 'Направление ветра',
        windGusts: 'Порывы ветра',

        weatherCondition: 'Состояние погоды',
        coordinates: 'Координаты',
        recommendation: 'Рекомендация',
        summary: 'Описание',

        clear: 'Ясно',
        partlyCloudy: 'Переменная облачность',
        cloudy: 'Облачно',
        rain: 'Дождь',
        heavyRain: 'Сильный дождь',
        snow: 'Снег',
        fog: 'Туман',
        storm: 'Шторм',

        requestFailed: 'Ошибка запроса.',
    },
};

export function resolveLocale(locale) {
    return messages[locale] ? locale : 'en';
}

export function translations(locale) {
    return messages[resolveLocale(locale)];
}