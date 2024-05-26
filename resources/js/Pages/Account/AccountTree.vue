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
        <span class="w-1/2":class="{'text-xl':account.level==0}":style="{'padding-left':account.level*1.5 +'rem'}" >
            {{ account.name }}
        </span>
        <span class="w-1/2" :class="{'text-xl':account.level==0}" >
                {{ Format(account.balance) }}
        </span> 
    </div>
    <div v-if="account.children" class="py-1" ></div>
    <div  v-if="account.children" v-for="(child,index) in account.children" :key="index" class=" ">
        <AccountTree :account="child"  >
        </AccountTree>
    </div>

   
  
    
    
   

  

</template>