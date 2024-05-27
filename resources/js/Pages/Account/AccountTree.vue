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
         <span>{{ account.name }}  </span>
         <button v-if="account.children" type="button" class="inline ml-5 text-sky-800" style="visibility: visible;" tabindex="-1" data-pc-section="rowtoggler" data-pd-ripple="true"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="p-icon p-tree-toggler-icon" aria-hidden="true" data-pc-section="rowtogglericon"><path d="M7.01744 10.398C6.91269 10.3985 6.8089 10.378 6.71215 10.3379C6.61541 10.2977 6.52766 10.2386 6.45405 10.1641L1.13907 4.84913C1.03306 4.69404 0.985221 4.5065 1.00399 4.31958C1.02276 4.13266 1.10693 3.95838 1.24166 3.82747C1.37639 3.69655 1.55301 3.61742 1.74039 3.60402C1.92777 3.59062 2.11386 3.64382 2.26584 3.75424L7.01744 8.47394L11.769 3.75424C11.9189 3.65709 12.097 3.61306 12.2748 3.62921C12.4527 3.64535 12.6199 3.72073 12.7498 3.84328C12.8797 3.96582 12.9647 4.12842 12.9912 4.30502C13.0177 4.48162 12.9841 4.662 12.8958 4.81724L7.58083 10.1322C7.50996 10.2125 7.42344 10.2775 7.32656 10.3232C7.22968 10.3689 7.12449 10.3944 7.01744 10.398Z" fill="currentColor"></path></svg></button>

        </span>
        <span class="w-1/2" :class="{'text-lg':account.level==0}" >
                {{ Format(account.balance) }}
        </span> 
    </div>
    <div  v-show="ShowChildren"  v-if="account.children" class="py-1" ></div>
    <div v-show="ShowChildren"  v-if="account.children" v-for="(child,index) in account.children" :key="index" class=" ">
        <AccountTree :account="child"  >
        </AccountTree>
    </div>

    

   
  
    
    
   

  

</template>