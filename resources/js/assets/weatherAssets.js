const icons = import.meta.glob('../../../assets/weatherIcons/*.png', { eager: true, query: '?url', import: 'default' });
const landscapes = import.meta.glob('../../../assets/landscape/*.webp', { eager: true, query: '?url', import: 'default' });

const fileKey = (path) => path.split('/').pop().replace(/\.(png|jpe?g|webp)$/i, '');

export const weatherIconMap = Object.fromEntries(Object.entries(icons).map(([path, url]) => [fileKey(path), url]));
export const landscapeMap = Object.fromEntries(Object.entries(landscapes).map(([path, url]) => [fileKey(path), url]));

export const logoUrl = new URL('../../../assets/logo.png', import.meta.url).href;