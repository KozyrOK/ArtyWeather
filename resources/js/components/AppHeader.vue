<script setup>
import { logoUrl } from '../assets/weatherAssets';

import { supportedLocales } from '../i18n';
const props = defineProps({ user: Object, settings: Object, t: Object, page: { type: String, default: 'weather' } });
const emit = defineEmits(['update-settings', 'logout', 'navigate']);
const currentLocale = () => props.settings?.user?.locale || props.user?.locale || 'en';
const currentTheme = () => props.settings?.user?.theme || props.user?.theme || 'light';
function toggleLocale() { const locale = currentLocale(); emit('update-settings', { locale: supportedLocales[(supportedLocales.indexOf(locale) + 1) % supportedLocales.length], theme: currentTheme() }); }
function toggleTheme() { emit('update-settings', { locale: currentLocale(), theme: currentTheme() === 'dark' ? 'light' : 'dark' }); }
</script>

<template><header class="app-header"><div class="app-header__inner"><button type="button" class="brand brand--button" @click="emit('navigate', 'weather')"><img :src="logoUrl" :alt="t.appName"><span class="brand__text"><strong>{{ t.appName }}</strong><span>{{ t.appDescription }}</span></span></button><div class="header-actions"><button type="button" class="header-control" :class="{ 'header-control--active': page === 'dashboard' }" @click="emit('navigate', 'dashboard')">{{ t.dashboard }}</button><button type="button" class="header-control" :aria-label="t.locale" @click="toggleLocale">{{ currentLocale().toUpperCase() }}</button><button type="button" class="header-control" :aria-label="t.theme" @click="toggleTheme">{{ currentTheme() === 'dark' ? t.light : t.dark }}</button><button type="button" class="header-control header-control--accent" @click="emit('logout')">{{ t.logout }}</button></div></div></header></template>