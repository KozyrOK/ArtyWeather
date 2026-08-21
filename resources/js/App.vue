<script setup>
import { computed, onMounted, ref, watchEffect } from 'vue';
import AppHeader from './components/AppHeader.vue';
import Dashboard from './components/Dashboard.vue';
import WeatherOverview from './components/WeatherOverview.vue';
import { api } from './services/apiClient';
import { useWeather } from './composables/useWeather';
import { resolveLocale, translations } from './i18n';

const user = ref(null);
const { state, enabled, forecast, load, refresh, saveSettings } = useWeather();
const locale = computed(() => resolveLocale(state.settings?.user?.locale || user.value?.locale || 'en'));
const theme = computed(() => state.settings?.user?.theme || user.value?.theme || 'system');
const t = computed(() => translations(locale.value));
async function logout() { await api.logout(); user.value = null; }
onMounted(async () => { try { user.value = (await api.me()).user; } catch {} await load(); });
watchEffect(() => {
  document.documentElement.lang = locale.value;
  document.documentElement.dataset.theme = theme.value;
});
</script>
<template><AppHeader :user="user" :settings="state.settings" :t="t" @update-settings="saveSettings" @logout="logout"/><div class="layout"><WeatherOverview :weather="state.weather" :presentation="state.presentation" :presentation-loading="state.presentationLoading" :enabled="enabled" :forecast="forecast" :loading="state.loading" :error="state.error" :t="t" @refresh="refresh"/><Dashboard :settings="state.settings" :saving="state.saving" :t="t" @save="saveSettings"/></div></template>