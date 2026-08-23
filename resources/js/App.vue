<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { logoUrl } from './assets/weatherAssets';

import AppHeader from './components/AppHeader.vue';
import Dashboard from './components/Dashboard.vue';
import LoginView from './components/LoginView.vue';
import WeatherOverview from './components/WeatherOverview.vue';

import { api, authToken } from './services/apiClient';
import { useWeather } from './composables/useWeather';
import { resolveLocale, translations } from './i18n';

const user = ref(null);
const authChecked = ref(false);
const authLoading = ref(false);

const { state, enabled, forecast, load, refresh, saveSettings } = useWeather();

const locale = computed(() =>
    resolveLocale(
        state.settings?.user?.locale ||
        user.value?.locale ||
        localStorage.getItem('artyweather_locale') ||
        'en'
    )
);

const theme = computed(() =>
    state.settings?.user?.theme ||
    user.value?.theme ||
    localStorage.getItem('artyweather_theme') ||
    'light'
);

const t = computed(() => translations(locale.value));

const authenticated = computed(() => Boolean(user.value));

function applyClientPreferences(nextLocale, nextTheme) {
    if (nextLocale) {
        localStorage.setItem('artyweather_locale', nextLocale);
    }

    if (nextTheme) {
        localStorage.setItem('artyweather_theme', nextTheme);
    }
}

async function initializeAuthenticatedUser() {
    if (!authToken.get()) {
        user.value = null;
        authChecked.value = true;

        return;
    }

    authLoading.value = true;

    try {
        const response = await api.me();

        user.value = response.user;
        applyClientPreferences(
            response.user?.locale,
            response.user?.theme
        );

        await load();
    } catch (error) {
        if (error?.status === 401) {
            authToken.clear();
            user.value = null;
        } else {
            state.error = error;
        }
    } finally {
        authLoading.value = false;
        authChecked.value = true;
    }
}

async function handleAuthenticated(authenticatedUser) {
    user.value = authenticatedUser;

    applyClientPreferences(
        authenticatedUser.locale,
        authenticatedUser.theme
    );

    await load();
}

async function handleLogout() {
    try {
        await api.logout();
    } finally {
        authToken.clear();
        user.value = null;
        state.weather = null;
        state.presentation = null;
        state.settings = null;
        state.error = null;
    }
}

async function handleLocaleChange(nextLocale) {
    applyClientPreferences(nextLocale, theme.value);

    if (!authenticated.value) {
        return;
    }

    await saveSettings({
        locale: nextLocale,
        theme: theme.value,
    });
}

async function handleSettingsChange(settings) {
    if (settings.locale) {
        applyClientPreferences(
            settings.locale,
            settings.theme || theme.value
        );
    }

    if (settings.theme) {
        applyClientPreferences(
            settings.locale || locale.value,
            settings.theme
        );
    }

    await saveSettings(settings);

    if (state.settings?.user) {
        user.value = {
            ...user.value,
            ...state.settings.user,
        };
    }
}

watch(theme, (value) => {
    const normalizedTheme = value === 'dark' ? 'dark' : 'light';

    document.documentElement.dataset.theme = normalizedTheme;
});

watch(locale, (value) => {
    document.documentElement.lang = value;
});

onMounted(initializeAuthenticatedUser);
</script>

<template>
    <div class="app-root" :style="{ '--app-logo-url': `url(${logoUrl})` }">
        <LoginView
            v-if="authChecked && !authenticated"
            :t="t"
            :locale="locale"
            @authenticated="handleAuthenticated"
            @locale-change="handleLocaleChange"
        />

        <div v-else-if="authenticated" class="authenticated-app">
            <AppHeader
                :user="user"
                :settings="state.settings"
                :t="t"
                @update-settings="handleSettingsChange"
                @logout="handleLogout"
            />

            <div class="app-page">
                <div class="app-page__intro">
                    <div>
                        <span class="eyebrow">
                            {{ t.appName }}
                        </span>

                        <h1>{{ t.weatherOverview }}</h1>
                    </div>
                </div>

                <div class="layout">
                    <WeatherOverview
                        :weather="state.weather"
                        :presentation="state.presentation"
                        :presentation-loading="state.presentationLoading"
                        :enabled="enabled"
                        :forecast="forecast"
                        :loading="state.loading"
                        :error="state.error"
                        :t="t"
                        @refresh="refresh"
                    />

                    <Dashboard
                        :settings="state.settings"
                        :saving="state.saving"
                        :t="t"
                        @save="handleSettingsChange"
                    />
                </div>
            </div>
        </div>

        <div v-else class="app-loading">
            <span>{{ t.loadingWeather }}</span>
        </div>
    </div>
</template>