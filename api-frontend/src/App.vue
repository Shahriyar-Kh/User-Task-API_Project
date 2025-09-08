<template>
<header>
<div class="container">
<Navbar />
</div>
</header>
<main>
<div class="container">
<router-view />
</div>
</main>
</template>


<script setup>
import Navbar from './components/Navbar.vue'
</script>