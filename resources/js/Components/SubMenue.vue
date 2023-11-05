<template>

  <div class="py-4  text-sky-400 border-gray-400 border-dotted border-b-2 ">
        <div  class="w-5/6 mx-auto flex justify-start align-middle ">  
            
            <slot  @click="open= !open" name="svg">
            </slot> 

            <div  @click="open= !open" class=" mx-2   text-base text-right min-w-fit font-semibold">
                <slot name="title">
                </slot>  
            </div> 

            <svg  @click="open= !open" :class="{' rotate-90': open }"  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 ml-auto transition-all ">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>

        
        </div>
        <div >
            <Transition name="slide-fade" >
                <div v-if="open" v-click-outside="close"  class="flex text-left flex-col justify-around items-center  mt-1  ">
                    <div  class=" w-full  text-sm  py-2 ">
                        <div  class=" w-11/12 my-3 mx-auto rounded-xl text-gray-100 ">
                            <slot    name="menue_items">

                            </slot>
                        </div> 
                    </div>   
                </div> 
            </Transition>        
        </div>
    </div>

</template>

<script>
 // import { Head, Link } from '@inertiajs/inertia-vue3';
 import { Head, Link, router } from '@inertiajs/vue3';
import {  Transition } from 'vue' 



 



export default  {
    props:{
    items:Object,    

    },
    components: {
            Head, Link,
        },
   
    data() {
        return {
        open: false, 
        }
    },
    methods :{
        close(){
            this.open = false
            console.log("hrer")
            console.log(this.open)
        },
    
    
   
    },
       
    
}
</script>
<style>
.slide-fade-enter-active ,


.slide-fade-leave-active {
  transition: opacity 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
}

</style>