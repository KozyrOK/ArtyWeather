<script setup>
import { logoUrl } from '../assets/weatherAssets';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },

    settings: {
        type: Object,
        default: null,
    },

    t: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    'update-settings',
    'logout',
]);

function currentLocale() {
    return props.settings?.user?.locale ||
        props.user?.locale ||
        'en';
}

function currentTheme() {
    return props.settings?.user?.theme ||
        props.user?.theme ||
        'light';
}

function toggleLocale() {
    emit('update-settings', {
        locale: currentLocale() === 'en' ? 'ru' : 'en',
        theme: currentTheme(),
    });
}

function toggleTheme() {
    emit('update-settings', {
        locale: currentLocale(),
        theme: currentTheme() === 'dark' ? 'light' : 'dark',
    });
}
</script>

<template>
    <header class="app-header">
        <div class="app-header__inner">
            <div class="brand">
                <img
                    :src="logoUrl"
                    :alt="t.appName"
                />

                <div class="brand__text">
                    <strong>{{ t.appName }}</strong>
                    <span>{{ t.appDescription }}</span>
                </div>
            </div>

            <div class="header-actions">
                <button
                    type="button"
                    class="header-control"
                    @click="toggleLocale"
                >
                    {{ currentLocale().toUpperCase() }}
                </button>

                <button
                    type="button"
                    class="header-control"
                    @click="toggleTheme"
                >
                    {{ currentTheme() === 'dark' ? t.light : t.dark }}
                </button>

                <button
                    type="button"
                    class="header-control header-control--accent"
                    @click="$emit('logout')"
                >
                    {{ t.logout }}
                </button>
            </div>
        </div>
    </header>
</template>