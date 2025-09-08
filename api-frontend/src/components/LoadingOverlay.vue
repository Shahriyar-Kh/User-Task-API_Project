<template>
  <div
    v-if="loading.isLoading"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
  </div>
</template>

<script setup>
import { useLoadingStore } from '@/stores/loading'
const loading = useLoadingStore()
</script>

<style scoped>
/* fallback spinner animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
