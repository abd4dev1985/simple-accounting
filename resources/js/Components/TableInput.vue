<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue,Raw
});
function focuse_down(){
    console.log(Raw)
    console.log("Raw")

}

</script>

<template>
    <div @click="focuse_down" class="bg-blue-700">
        <input
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class=" bg-pink-400  py-4 w-28 max-w-max text-center rounded-md ring-offset-1 focus:ring-2 "
        >  
    </div>
    
</template>
