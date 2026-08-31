<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { logoUrl } from './assets/weatherAssets';

import AppHeader from './components/AppHeader.vue'; import Dashboard from './components/Dashboard.vue'; import LoginView from './components/LoginView.vue'; import WeatherOverview from './components/WeatherOverview.vue';
import { api, authToken } from './services/apiClient'; import { useWeather } from './composables/useWeather'; import { resolveLocale, translations } from './i18n';

const pageFromLocation = () => window.location.hash === '#dashboard' ? 'dashboard' : 'weather';
const user = ref(null); const authChecked = ref(false); const authLoading = ref(false); const page = ref(pageFromLocation());

const { state, enabled, forecast, load, refresh, saveSettings } = useWeather();

const locale = computed(() => resolveLocale(state.settings?.user?.locale || user.value?.locale || localStorage.getItem('artyweather_locale') || 'en'));
const theme = computed(() => state.settings?.user?.theme || user.value?.theme || localStorage.getItem('artyweather_theme') || 'light'); const t = computed(() => translations(locale.value)); const authenticated = computed(() => Boolean(user.value));
function applyClientPreferences(nextLocale, nextTheme) { if (nextLocale) localStorage.setItem('artyweather_locale', nextLocale); if (nextTheme) localStorage.setItem('artyweather_theme', nextTheme); }
function syncPageWithLocation() { page.value = pageFromLocation(); }
function navigate(nextPage) {
    const destination = nextPage === 'dashboard' ? 'dashboard' : 'weather';
    const targetHash = destination === 'dashboard' ? '#dashboard' : '';

    if (window.location.hash === targetHash && page.value === destination) {
        return;
    }

    window.location.hash = targetHash;
}
async function initializeAuthenticatedUser() { if (!authToken.get()) { user.value = null; authChecked.value = true; return; } authLoading.value = true; try { const response = await api.me(); user.value = response.user; applyClientPreferences(response.user?.locale, response.user?.theme); await load(); } catch (error) { if (error?.status === 401) { authToken.clear(); user.value = null; } else state.error = error; } finally { authLoading.value = false; authChecked.value = true; } }
async function handleAuthenticated(authenticatedUser) { user.value = authenticatedUser; applyClientPreferences(authenticatedUser.locale, authenticatedUser.theme); await load(); }
async function handleLogout() { try { await api.logout(); } finally { authToken.clear(); user.value = null; state.weather = null; state.presentation = null; state.settings = null; state.error = null; navigate('weather'); } }
async function handleLocaleChange(nextLocale) { applyClientPreferences(nextLocale, theme.value); if (authenticated.value) await saveSettings({ locale: nextLocale, theme: theme.value }); }
async function handleThemeChange(nextTheme) { applyClientPreferences(locale.value, nextTheme); if (authenticated.value) await saveSettings({ locale: locale.value, theme: nextTheme }); }
async function handleSettingsChange(settings) { applyClientPreferences(settings.locale || locale.value, settings.theme || theme.value); await saveSettings(settings); if (state.settings?.user) user.value = { ...user.value, ...state.settings.user }; }
watch(theme, (value) => { document.documentElement.dataset.theme = value === 'dark' ? 'dark' : 'light'; }, { immediate: true }); watch(locale, (value) => { document.documentElement.lang = value; }, { immediate: true }); onMounted(() => { window.addEventListener('hashchange', syncPageWithLocation); window.addEventListener('popstate', syncPageWithLocation); initializeAuthenticatedUser(); }); onBeforeUnmount(() => { window.removeEventListener('hashchange', syncPageWithLocation); window.removeEventListener('popstate', syncPageWithLocation); });

</script>

<template>
    <div class="app-root" :style="{ '--app-logo-url': `url(${logoUrl})` }">
        <LoginView v-if="authChecked && !authenticated" :t="t" :locale="locale" :theme="theme" @authenticated="handleAuthenticated" @locale-change="handleLocaleChange" @theme-change="handleThemeChange"/>
            <div v-else-if="authenticated" class="authenticated-app">
                <AppHeader :user="user" :settings="state.settings" :t="t" :page="page" @update-settings="handleSettingsChange" @logout="handleLogout" @navigate="navigate"/>
                    <div class="app-page">
                        <WeatherOverview v-if="page === 'weather'" :weather="state.weather" :presentation="state.presentation" :presentation-loading="state.presentationLoading" :enabled="enabled" :forecast="forecast" :forecast-period="state.settings?.forecast_period" :loading="state.loading" :error="state.error" :t="t" :locale="locale" @refresh="refresh"/>
                        <Dashboard v-else :settings="state.settings" :saving="state.saving" :t="t" @navigate="navigate" @save="handleSettingsChange"/>
                        </div></div>
                        <div v-else class="app-loading">
                        <span>{{ t.loadingWeather }}</span>
                        </div></div>
</template>