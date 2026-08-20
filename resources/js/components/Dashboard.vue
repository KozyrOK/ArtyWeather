<script setup>
import { reactive, watchEffect } from 'vue';
const props = defineProps({ settings: Object, saving: Boolean });
const emit = defineEmits(['save']);
const form = reactive({});
watchEffect(() => { if (props.settings) Object.assign(form, { latitude: props.settings.latitude, longitude: props.settings.longitude, forecast_period: props.settings.forecast_period, locale: props.settings.user?.locale || 'en', theme: props.settings.user?.theme || 'system', ...props.settings.display }); });
const parameters = ['temperature','apparent_temperature','relative_humidity','precipitation','weather_code','cloud_cover','pressure','wind_speed','wind_direction','wind_gusts'];
</script>
<template><aside class="card dashboard"><h2>Dashboard</h2><form v-if="settings" @submit.prevent="emit('save', form)"><label>Latitude<input v-model.number="form.latitude" type="number" step="0.00001"></label><label>Longitude<input v-model.number="form.longitude" type="number" step="0.00001"></label><label>Forecast days<input v-model.number="form.forecast_period" min="1" max="16" type="number"></label><label>Locale<select v-model="form.locale"><option value="en">English</option><option value="ru">Русский</option></select></label><label>Theme<select v-model="form.theme"><option value="system">System</option><option value="light">Light</option><option value="dark">Dark</option></select></label><fieldset><legend>Weather parameters</legend><label v-for="parameter in parameters" :key="parameter" class="check"><input v-model="form[parameter]" type="checkbox"> {{ parameter.replaceAll('_', ' ') }}</label></fieldset><button :disabled="saving">{{ saving ? 'Saving…' : 'Save settings' }}</button></form><p v-else>Settings are loading…</p></aside></template>
