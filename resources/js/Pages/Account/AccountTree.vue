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
        return formatter.format(value)
    }else{  
        return 0 
    }
}




       



let severity_style= ref('');
//define computed props
const page = usePage()
const currencies= (page.props.currencies)? page.props.currencies:[];

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.CONTAINS },
   
});
// let MarginLeft = 'ml-'+props.account.level*3

</script>

<template>
    <div class="flex justify-start space-x-4 w-full"  >
        <span @click="ShowChildren=!ShowChildren" class="w-1/2 ":class="{'text-lg':account.level==0}" :style="{'padding-left':account.level*1.5 +'rem'}" >
            <svg  v-if="account.children"   data-slot="icon" class="inline h-3 w-4 rotate-90 mr-1 fill-slate-500" aria-hidden="true" fill="" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span >{{ account.name }}  </span>
        </span>
        <span class="w-1/2" :class="{'text-lg':account.level==0}" >
            {{ Format(account.balance) }}
        </span> 
    </div>
    <div  v-show="ShowChildren"  v-if="account.children" class="py-1" ></div>
    <div v-show="ShowChildren"  v-if="account.children" v-for="(child,index) in account.children" :key="index" class="bg-sky-100 text-sm ">
        <AccountTree :account="child"  >
        </AccountTree>
    </div>

    <slot></slot> 

   
  
    
    
   

  

</template>