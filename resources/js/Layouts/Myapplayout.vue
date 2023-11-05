<script setup>
import { ref } from 'vue';


defineProps({
    title: String,
});

const showingNaveBar = ref(false);


</script>

<template>
<div>
    <p > abd layoutnmnm {{title}}</p>
   
    <main>
        <slot />
    </main>
</div>
</template>
