<script setup>
import { computed, reactive, ref } from 'vue';
import { authToken, api } from '../services/apiClient';

const props = defineProps({
    t: {
        type: Object,
        required: true,
    },
    locale: {
        type: String,
        default: 'en',
    },
});

const emit = defineEmits(['authenticated', 'locale-change']);

const form = reactive({
    email: '',
    password: '',
});

const loading = ref(false);
const error = ref('');

const title = computed(() => props.t.welcomeTitle);
const text = computed(() => props.t.welcomeText);

function toggleLocale() {
    emit('locale-change', props.locale === 'en' ? 'ru' : 'en');
}

async function submit() {
    error.value = '';
    loading.value = true;

    try {
        const response = await api.login({
            email: form.email,
            password: form.password,
        });

        if (!response?.token || !response?.user) {
            throw new Error(props.t.requestFailed);
        }

        authToken.set(response.token);

        emit('authenticated', response.user);
    } catch (exception) {
        error.value =
            exception?.status === 422
                ? props.t.invalidCredentials
                : exception?.message || props.t.requestFailed;
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <main class="public-page">
        <section class="public-shell">
            <div class="public-visual">
                <div class="public-brand">
                    <span class="public-brand__mark">A</span>

                    <div>
                        <strong>{{ t.appName }}</strong>
                        <span>{{ t.appDescription }}</span>
                    </div>
                </div>

                <div class="public-visual__copy">
                    <span class="eyebrow">WEATHER EXPERIENCE</span>
                    <h1>{{ title }}</h1>
                    <p>{{ text }}</p>
                </div>

                <div class="public-visual__glow"></div>
            </div>

            <div class="public-form">
                <div class="public-form__top">
                    <div>
                        <span class="eyebrow">{{ t.login }}</span>
                        <h2>{{ t.signIn }}</h2>
                    </div>

                    <button
                        type="button"
                        class="icon-button"
                        :aria-label="t.locale"
                        @click="toggleLocale"
                    >
                        {{ locale.toUpperCase() }}
                    </button>
                </div>

                <form @submit.prevent="submit">
                    <label>
                        <span>{{ t.email }}</span>

                        <input
                            v-model.trim="form.email"
                            type="email"
                            autocomplete="username"
                            required
                        />
                    </label>

                    <label>
                        <span>{{ t.password }}</span>

                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            required
                        />
                    </label>

                    <p v-if="error" class="form-error">
                        {{ error }}
                    </p>

                    <button
                        class="primary-button"
                        type="submit"
                        :disabled="loading"
                    >
                        {{ loading ? t.signingIn : t.signIn }}
                    </button>
                </form>
            </div>
        </section>
    </main>
</template>