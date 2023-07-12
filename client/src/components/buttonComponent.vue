<script setup>
defineProps(['type', 'color', 'customClass'])
</script>

<template>
  <button
      class="rounded-md px-3.5 bg-indigo-400 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
      :class="customClass"
      :type="type">
    <slot></slot>
  </button>
</template>

<style scoped>

</style>