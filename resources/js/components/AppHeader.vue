<script setup>
import { logoUrl } from '../assets/weatherAssets';
defineProps({ user: Object, settings: Object });
const emit = defineEmits(['update-settings', 'logout']);
</script>
<template>
  <header class="app-header"><div class="brand"><img :src="logoUrl" alt="ArtyWeather logo"><strong>ArtyWeather</strong></div><div class="header-actions"><select :value="settings?.user?.locale || user?.locale || 'en'" @change="emit('update-settings', { locale: $event.target.value })"><option value="en">EN</option><option value="ru">RU</option></select><select :value="settings?.user?.theme || user?.theme || 'system'" @change="emit('update-settings', { theme: $event.target.value })"><option value="system">System</option><option value="light">Light</option><option value="dark">Dark</option></select><button @click="emit('logout')">Logout</button></div></header>
</template>