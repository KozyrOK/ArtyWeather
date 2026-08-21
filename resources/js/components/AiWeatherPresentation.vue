<script setup>
import { computed } from 'vue';
import { landscapeMap, weatherIconMap } from '../assets/weatherAssets';

const props = defineProps({ presentation: Object, loading: Boolean, t: Object });

const data = computed(() => props.presentation?.presentation ?? props.presentation?.fallback ?? null);
const status = computed(() => props.presentation?.status ?? 'processing');
const icon = computed(() => weatherIconMap[data.value?.weather_icon]);
const landscape = computed(() => landscapeMap[data.value?.landscape]);
</script>

<template>
  <section class="card ai-presentation">
    <div class="ai-presentation__media">
      <img v-if="landscape" :src="landscape" :alt="data?.landscape" class="ai-presentation__landscape">
      <img v-if="icon" :src="icon" :alt="data?.weather_icon" class="ai-presentation__icon">
    </div>
    <div>
      <p class="eyebrow">{{ t.aiPresentation }}</p>
      <h2>{{ status === 'ready' ? t.ready : t.processing }}</h2>
      <p v-if="loading">{{ t.loadingPresentation }}</p>
      <template v-else-if="data">
        <p>{{ data.summary }}</p>
        <strong>{{ data.recommendation }}</strong>
      </template>
      <p v-else>{{ t.presentationFallback }}</p>
    </div>
  </section>
</template>
