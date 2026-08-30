const messages = {
    en: {
        appName: 'ArtyWeather', appDescription: 'Weather, interpreted beautifully.',
        welcomeTitle: 'Welcome to ArtyWeather', welcomeText: 'Get a clear weather forecast for your location with a clean interface and intelligent presentation.',
        weatherExperience: 'Weather experience', login: 'Login', logout: 'Logout', email: 'Email', password: 'Password', signIn: 'Sign in', signingIn: 'Signing in…', invalidCredentials: 'The email or password is incorrect.', loginRequired: 'Please sign in to continue.',
        dashboard: 'Dashboard', backToWeather: 'Back to weather', weatherOverview: 'Weather overview', currentWeather: 'Current weather', refreshWeather: 'Refresh weather', loadingWeather: 'Loading weather…', emptyWeather: 'No weather data available.',
        latitude: 'Latitude', longitude: 'Longitude', forecastDays: 'Forecast days', locale: 'Language', theme: 'Theme', light: 'Light', dark: 'Dark', weatherParameters: 'Weather parameters', saveSettings: 'Save settings', saving: 'Saving…', settingsLoading: 'Settings are loading…',
        aiPresentation: 'AI presentation', ready: 'Ready', processing: 'Processing', loadingPresentation: 'Preparing AI presentation…', presentationFallback: 'A weather presentation is not available right now. Please refresh shortly.',
        forecast: 'Forecast', detailedForecast: 'Detailed Forecast', charts: 'Weather charts', time: 'Time', noForecast: 'No forecast data available.', temperature: 'Temperature', apparentTemperature: 'Feels like', relativeHumidity: 'Humidity', precipitation: 'Precipitation', weatherCode: 'Weather code', cloudCover: 'Cloud cover', pressure: 'Pressure', windSpeed: 'Wind speed', windDirection: 'Wind direction', windGusts: 'Wind gusts', weatherCondition: 'Weather condition', coordinates: 'Coordinates', recommendation: 'Recommendation', summary: 'Summary',
        clear: 'Clear', partlyCloudy: 'Partly cloudy', cloudy: 'Cloudy', rain: 'Rain', heavyRain: 'Heavy rain', snow: 'Snow', fog: 'Fog', storm: 'Storm', requestFailed: 'Request failed.',
    },
     ru: {
        appName: 'ArtyWeather', appDescription: 'Погода, представленная красиво.',
        welcomeTitle: 'Добро пожаловать в ArtyWeather', welcomeText: 'Получайте понятный прогноз погоды для выбранного местоположения в удобном интерфейсе с интеллектуальным представлением данных.',
        weatherExperience: 'Погода рядом', login: 'Вход', logout: 'Выйти', email: 'Электронная почта', password: 'Пароль', signIn: 'Войти', signingIn: 'Выполняется вход…', invalidCredentials: 'Неверный адрес электронной почты или пароль.', loginRequired: 'Для продолжения необходимо войти.',
        dashboard: 'Настройки', backToWeather: 'К погоде', weatherOverview: 'Обзор погоды', currentWeather: 'Текущая погода', refreshWeather: 'Обновить погоду', loadingWeather: 'Загружаем погоду…', emptyWeather: 'Нет данных о погоде.',
        latitude: 'Широта', longitude: 'Долгота', forecastDays: 'Дней прогноза', locale: 'Язык', theme: 'Тема', light: 'Светлая', dark: 'Тёмная', weatherParameters: 'Погодные параметры', saveSettings: 'Сохранить настройки', saving: 'Сохраняем…', settingsLoading: 'Настройки загружаются…',
        aiPresentation: 'AI-представление', ready: 'Готово', processing: 'Обрабатывается', loadingPresentation: 'Подготавливаем AI-представление…', presentationFallback: 'Погодное представление сейчас недоступно. Попробуйте обновить страницу чуть позже.',
        forecast: 'Прогноз', detailedForecast: 'Подробный прогноз', charts: 'Графики погоды', time: 'Время', noForecast: 'Нет данных прогноза.', temperature: 'Температура', apparentTemperature: 'Ощущается как', relativeHumidity: 'Влажность', precipitation: 'Осадки', weatherCode: 'Код погоды', cloudCover: 'Облачность', pressure: 'Давление', windSpeed: 'Скорость ветра', windDirection: 'Направление ветра', windGusts: 'Порывы ветра', weatherCondition: 'Состояние погоды', coordinates: 'Координаты', recommendation: 'Рекомендация', summary: 'Описание',
        clear: 'Ясно', partlyCloudy: 'Переменная облачность', cloudy: 'Облачно', rain: 'Дождь', heavyRain:('Сильный дождь'), snow:('Снег'), fog:('Туман'), storm:('Шторм'), requestFailed:('Ошибка запроса.'),
    },
    uk: {
        appName: 'ArtyWeather', appDescription: 'Погода, представлена красиво.',
        welcomeTitle: 'Ласкаво просимо до ArtyWeather', welcomeText: 'Отримуйте зрозумілий прогноз погоди для вибраного місця у зручному інтерфейсі з інтелектуальним поданням даних.',
        weatherExperience: 'Погода поруч', login: 'Вхід', logout: 'Вийти', email: 'Електронна пошта', password: 'Пароль', signIn: 'Увійти', signingIn: 'Виконується вхід…', invalidCredentials: 'Неправильна електронна пошта або пароль.', loginRequired: 'Щоб продовжити, увійдіть.',
        dashboard: 'Налаштування', backToWeather: 'До погоди', weatherOverview: 'Огляд погоди', currentWeather: 'Поточна погода', refreshWeather: 'Оновити погоду', loadingWeather: 'Завантажуємо погоду…', emptyWeather: 'Немає даних про погоду.',
        latitude: 'Широта', longitude: 'Довгота', forecastDays: 'Днів прогнозу', locale: 'Мова', theme: 'Тема', light: 'Світла', dark: 'Темна', weatherParameters: 'Параметри погоди', saveSettings: 'Зберегти налаштування', saving: 'Зберігаємо…', settingsLoading: 'Завантажуємо налаштування…',
        aiPresentation: 'AI-подання', ready: 'Готово', processing: 'Обробляється', loadingPresentation: 'Готуємо AI-подання…', presentationFallback: 'Подання погоди зараз недоступне. Спробуйте оновити сторінку трохи пізніше.',
        forecast: 'Прогноз', detailedForecast: 'Детальний прогноз', charts: 'Графіки погоди', time: 'Час', noForecast: 'Немає даних прогнозу.', temperature: 'Температура', apparentTemperature: 'Відчувається як', relativeHumidity: 'Вологість', precipitation: 'Опади', weatherCode: 'Код погоди', cloudCover: 'Хмарність', pressure: 'Тиск', windSpeed: 'Швидкість вітру', windDirection: 'Напрямок вітру', windGusts: 'Пориви вітру', weatherCondition: 'Стан погоди', coordinates: 'Координати', recommendation: 'Рекомендація', summary: 'Опис',
        clear: 'Ясно', partlyCloudy: 'Мінлива хмарність', cloudy: 'Хмарно', rain: 'Дощ', heavyRain: 'Сильний дощ', snow: 'Сніг', fog:('Туман'), storm:('Шторм'), requestFailed:('Ошибка запроса.'),
     },
 };

export function resolveLocale(locale) { return messages[locale] ? locale : 'en'; }
export function translations(locale) { return messages[resolveLocale(locale)]; }
export const supportedLocales = ['en', 'ru', 'uk'];