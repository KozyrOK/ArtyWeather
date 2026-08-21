const messages = {
  en: {
    loadingWeather: 'Loading weather…',
    refreshWeather: 'Refresh weather',
    emptyWeather: 'Empty weather data.',
    dashboard: 'Dashboard',
    latitude: 'Latitude',
    longitude: 'Longitude',
    forecastDays: 'Forecast days',
    locale: 'Locale',
    theme: 'Theme',
    system: 'System',
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
    presentationFallback: 'Presentation is being generated. Weather facts remain available.',
    logout: 'Logout',
  },
  ru: {
    loadingWeather: 'Загружаем погоду…',
    refreshWeather: 'Обновить погоду',
    emptyWeather: 'Нет данных о погоде.',
    dashboard: 'Панель настроек',
    latitude: 'Широта',
    longitude: 'Долгота',
    forecastDays: 'Дней прогноза',
    locale: 'Язык',
    theme: 'Тема',
    system: 'Системная',
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
    presentationFallback: 'Представление генерируется. Фактические погодные данные доступны.',
    logout: 'Выйти',
  },
};

export function resolveLocale(locale) {
  return messages[locale] ? locale : 'en';
}

export function translations(locale) {
  return messages[resolveLocale(locale)];
}
