<template>
  <form @submit.prevent="submit">
    <input v-model.trim="local.title" type="text" placeholder="Title" required />
    <textarea v-model.trim="local.description" placeholder="Description"></textarea>
    <div class="text-right">
      <button type="button" class="btn" @click="$emit('cancel')">Cancel</button>
      <button type="submit" class="btn primary">Save</button>
    </div>
  </form>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  modelValue: { type: Object, default: () => ({ title: '', description: '' }) },
})
const emit = defineEmits(['update:modelValue', 'save', 'cancel'])

const local = reactive({ title: '', description: '' })

// keep local in sync with modelValue
watch(
  () => props.modelValue,
  (v) => {
    local.title = v?.title || ''
    local.description = v?.description || ''
  },
  { immediate: true, deep: true }
)

function submit() {
  emit('update:modelValue', { ...local })
  emit('save', { ...local })
}
</script>
