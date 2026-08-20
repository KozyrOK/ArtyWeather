import { computed, reactive } from 'vue';
import { api } from '../services/apiClient';

export function useWeather() {
  const state = reactive({ weather: null, settings: null, loading: true, refreshing: false, saving: false, error: null });
  const enabled = computed(() => state.settings?.display ?? state.weather?.settings?.display ?? {});
  const forecast = computed(() => state.weather?.snapshot?.forecast ?? []);

  async function load() {
    state.loading = true; state.error = null;
    try { [state.settings, state.weather] = await Promise.all([api.settings(), api.weather()]); }
    catch (error) { state.error = error; }
    finally { state.loading = false; }
  }
  async function refresh() {
    state.refreshing = true; state.error = null;
    try { state.weather = await api.refreshWeather(); }
    catch (error) { state.error = error; }
    finally { state.refreshing = false; }
  }
  async function saveSettings(settings) {
    state.saving = true; state.error = null;
    try { state.settings = await api.updateSettings(settings); state.weather = await api.weather(); }
    catch (error) { state.error = error; }
    finally { state.saving = false; }
  }
  return { state, enabled, forecast, load, refresh, saveSettings };
}
