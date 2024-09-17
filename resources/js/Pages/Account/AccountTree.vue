<script setup>

import { reactive ,computed,watch,onMounted,ref,defineAsyncComponent  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import DateObject from '../../DateObject.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';   
import Row from 'primevue/row';                   
import { FilterMatchMode } from 'primevue/api';
import Button from 'primevue/button';

// const RecursiveChildLazy = defineAsyncComponent(() => import('./RecursiveChild.vue'))


let props =defineProps({
    account:{   } ,
    without_balance:{  default:false }
})
let ShowChildren =ref(false)


let ToogelShowChildren= ()=>{
    if (props.account.children) {
        ShowChildren.value = !ShowChildren.value
    }
}

// format number and make more readable for human by addind comma  
function Format(value){
    let formatter =Intl.NumberFormat('en')
    if (  !isNaN(Number(value))  &&  Number(value) != 0    ) {
        let number = Math.trunc(value)  
       // number = Math.abs(number)
        return formatter.format(number)
    }else{  
        return 0 
    }
}


// let MarginLeft = 'ml-'+props.account.level*3

let PaddingLift = function(level){
    return level*1.5 +'rem'
}


const my_bg = ref(null)
onMounted(() => {
   if (props.account.level==0) {
    ShowChildren.value =true
   }
  
})

</script>

<template>
    

    <div class="flex justify-start gap-4 mobile:gap-2 w-full text-gray-700 my-3  " ref="my_bg"  >
        <span @click="ShowChildren=!ShowChildren" class="w-2/3 " :class="{'text-2xl font-semibold':account.level==0  }"    
        :style="{'padding-left':  PaddingLift(account.level)}" >
            <svg  v-if="account.children"   data-slot="icon" class="inline h-3 w-4 rotate-90 mr-1 fill-slate-500" aria-hidden="true" fill="" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span >{{ account.name }}  </span>
        </span>

        <span v-show="!ShowChildren && !without_balance "  class="w-1/3" >
            {{ Format(account.balance) }} 
        </span> 
        <span  v-show="without_balance" class="bg-orange-200"  >
            <slot name="control_panel" >
            </slot>
        </span>
        
    </div>

    <div  v-show="ShowChildren"  v-if="account.children && !without_balance" class="py-1" >
        <div v-for="(child,index) in account.children" :key="index" class="text-base  ">
            <AccountTree :account="child" :without_balance="without_balance"  >
            </AccountTree>
        </div>
        <div class=" flex justify-start space-x-4 w-full border-b-2 border-gray-400 font-bold " ref="my_bg"   >
            <span  class="w-2/3" :style="{'padding-left': PaddingLift(account.level)}" >
                Total {{ account.name }}  
            </span>
            <span  class="w-1/3 ">
                {{ Format(account.balance) }} 
            </span> 
        </div>
    </div>

    <div  v-show="ShowChildren"  v-if="account.children && without_balance" class="py-1" >
        <div v-for="(child,index) in account.children" :key="index" class="text-base my-1">
            <AccountTree :account="child" :without_balance="without_balance"  >
            </AccountTree>
        </div>
    </div>

    



   

    
    

    

    <slot></slot> 

   
  
    
    
   

  

</template>