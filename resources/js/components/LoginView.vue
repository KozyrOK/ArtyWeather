<script setup>
import { computed, reactive, ref } from 'vue';
import { logoUrl } from '../assets/weatherAssets';
import { authToken, api } from '../services/apiClient';
import { supportedLocales } from '../i18n';

const props = defineProps({ t: Object, locale: { type: String, default: 'en' }, theme: { type: String, default: 'light' } });
const emit = defineEmits(['authenticated', 'locale-change', 'theme-change']);
const form = reactive({ email: '', password: '' }); const loading = ref(false); const error = ref('');
const title = computed(() => props.t.welcomeTitle); const text = computed(() => props.t.welcomeText);
function nextLocale() { emit('locale-change', supportedLocales[(supportedLocales.indexOf(props.locale) + 1) % supportedLocales.length]); }
async function submit() { error.value = ''; loading.value = true; try { const response = await api.login(form); if (!response?.token || !response?.user) throw new Error(props.t.requestFailed); authToken.set(response.token); emit('authenticated', response.user); } catch (exception) { error.value = exception?.status === 422 ? props.t.invalidCredentials : exception?.message || props.t.requestFailed; } finally { loading.value = false; } }

</script>

<template><main class="public-page"><section class="public-shell"><div class="public-visual"><div class="public-brand"><img :src="logoUrl" :alt="t.appName"><div><strong>{{ t.appName }}</strong><span>{{ t.appDescription }}</span></div></div><div class="public-visual__copy"><span class="eyebrow">{{ t.weatherExperience }}</span><h1>{{ title }}</h1><p>{{ text }}</p></div><div class="public-visual__glow"></div></div><div class="public-form"><div class="public-form__top"><div><span class="eyebrow">{{ t.login }}</span><h2>{{ t.signIn }}</h2></div><div class="public-controls"><button type="button" class="icon-button" :aria-label="t.locale" @click="nextLocale">{{ locale.toUpperCase() }}</button><button type="button" class="icon-button" :aria-label="t.theme" @click="emit('theme-change', theme === 'dark' ? 'light' : 'dark')">{{ theme === 'dark' ? t.light : t.dark }}</button></div></div><form @submit.prevent="submit"><label><span>{{ t.email }}</span><input v-model.trim="form.email" type="email" autocomplete="username" required></label><label><span>{{ t.password }}</span><input v-model="form.password" type="password" autocomplete="current-password" required></label><p v-if="error" class="form-error">{{ error }}</p><button class="primary-button" type="submit" :disabled="loading">{{ loading ? t.signingIn : t.signIn }}</button></form></div></section></main></template>