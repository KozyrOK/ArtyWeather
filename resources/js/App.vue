<script setup>
import { onMounted, ref } from 'vue';
import AppHeader from './components/AppHeader.vue';
import Dashboard from './components/Dashboard.vue';
import WeatherOverview from './components/WeatherOverview.vue';
import { api } from './services/apiClient';
import { useWeather } from './composables/useWeather';

const user = ref(null);
const { state, enabled, forecast, load, refresh, saveSettings } = useWeather();
async function logout() { await api.logout(); user.value = null; }
onMounted(async () => { try { user.value = (await api.me()).user; } catch {} await load(); });
</script>
<template><AppHeader :user="user" :settings="state.settings" @update-settings="saveSettings" @logout="logout"/><div class="layout"><WeatherOverview :weather="state.weather" :presentation="state.presentation" :presentation-loading="state.presentationLoading" :enabled="enabled" :forecast="forecast" :loading="state.loading" :error="state.error" @refresh="refresh"/><Dashboard :settings="state.settings" :saving="state.saving" @save="saveSettings"/></div></template>