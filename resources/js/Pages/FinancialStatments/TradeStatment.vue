<script setup>

import { reactive ,computed,watch,onMounted,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import AppLayout from '@/Layouts/AppLayout.vue';

import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';

import ColumnGroup from 'primevue/columngroup';   
import Row from 'primevue/row';                   
import { FilterMatchMode } from 'primevue/api';
import Button from 'primevue/button';
import TreeTable from 'primevue/treetable';
import Tree from 'primevue/tree';
import AccountTree from '@/pages/Account/AccountTree.vue';

import Column from 'primevue/column';


let props =defineProps({
  accounts:{   } ,

})
const expandedKeys = {1:true};
const toggleApplications = () => {
    let _expandedKeys = { ...expandedKeys.value };
    if (_expandedKeys['0']) delete _expandedKeys['0'];
    else _expandedKeys['0'] = true;

    expandedKeys.value = _expandedKeys;
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


let Total_Avilable_Goods_for_Sale = computed(()=>{
  return  Number(props.accounts.Beginning_Inventory.balance)+Number(props.accounts.Net_Purchases.balance) 
})

let Cost_Of_Sold_Inventory = computed(()=>{
  return  Total_Avilable_Goods_for_Sale.value - Number(props.accounts.Ending_Iventory_cost) 
})

let Net_trade_STATMENT = computed(()=>{
  return  -1*Number(props.accounts.Net_Sales.balance)-Cost_Of_Sold_Inventory.value 
         
})

const dt = ref();
const exportCSV = () => { dt.value.exportCSV()}


let severity_style= ref('');
//define computed props
const page = usePage()
const currencies= (page.props.currencies)? page.props.currencies:[];


const formatter =Intl.NumberFormat('en')
function format_number( value ){
  if (  !isNaN(Number(value))  &&  Number(value) != 0    ) {
    return formatter.format(value)
  }else{  
    return null 
  }

}
function unformat_number( value ){
  if ( isNaN(value )) {
    return formatter.format(value)
  }else{  
    return null 
  }

}


</script>

<template>
  

    <div class=" mt-2 " >
      <div class="flex justify-start space-x-4 w-full my-3 even:bg-gray-200 " >
        <span  class="inline-block w-1/2 text-xl ">  Name </span>
        <span class=" inline-block w-1/2 text-xl">  Ammount   </span> 
      </div>
      
      <div class="flex justify-start space-x-4 w-full even:bg-gray-200" >
        <span  class="w-1/2 text-lg border">{{ accounts.Beginning_Inventory.name }}</span>
        <span class="w-1/2 text-lg border">{{ Format(accounts.Beginning_Inventory.balance) }}</span> 
      </div> 

      

      
      <div class="even:bg-gray-200 border-b-2  ">
        <AccountTree :account="accounts.Net_Purchases"  >
        </AccountTree>
      </div>

      <div class="flex justify-start space-x-4 w-full even:bg-gray-200" >
        <span  class="w-1/2 text-lg"> Total Avilable Goods for Sale</span>
        <span class="w-1/2 text-lg">{{ Format(Total_Avilable_Goods_for_Sale) }}</span> 
      </div>

      <div class="flex justify-start space-x-4 w-full even:bg-gray-200" >
        <span  class="w-1/2 text-lg"> Ending Iventory Cost</span>
        <span class="w-1/2 text-lg">{{ Format(accounts.Ending_Iventory_cost) }}</span> 
      </div>

      <div class="flex justify-start space-x-4 w-full even:bg-gray-200 " >
        <span  class="w-1/2 text-lg">Cost Of Sold Inventory</span>
        <span class="w-1/2 text-lg">{{ Format(Cost_Of_Sold_Inventory) }}</span> 
      </div>


      <div>
          <AccountTree :account="accounts.Net_Sales"  >
          </AccountTree>
      </div>



      <div>Net_trade_STATMENT  {{ Format(Net_trade_STATMENT) }}</div>


        
    </div>


</template>