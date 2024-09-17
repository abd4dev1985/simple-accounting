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
    let number = Math.abs(value)
    return formatter.format(number)
  }else{  
    return 0 
  }

}
function unformat_number( value ){
  if ( isNaN(value )) {
    return formatter.format(value)
  }else{  
    return null 
  }
}
let ShowSalesGroup = ref(false)
let Show_Cost_of_Sale_Group = ref(false)



</script>

<template>
  

    <div class=" " >
      <div class="flex gap-2 font-bold my-0.5 py-2 bg-gray-200   ">
        <div class="w-1/2"> 
          <svg  @click="ShowSalesGroup = !ShowSalesGroup" data-slot="icon"
           :class="{ 'rotate-90' :! ShowSalesGroup}"
           class="inline h-3 w-4 mr-1 fill-slate-500" aria-hidden="true" fill="" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
          {{ accounts.Net_Sales.name }}</div>
        <div class="w-1/2" > {{format_number(accounts.Net_Sales.balance) }}</div>
      </div>

      <Transition  name="slide-fade">
        <div v-if="ShowSalesGroup">
          <div  v-for="account in accounts.Net_Sales.children " class="flex gap-2 my-2 ">
            <div class="w-1/2"> {{ account.name }}</div>
            <div class="w-1/2" > {{format_number(account.balance) }}</div>
          </div>
        </div>
      </Transition>
      
      <div class="flex gap-2 font-semibold bg-gray-200 py-2 ">
        <div class="w-1/2">
          <svg  @click="Show_Cost_of_Sale_Group = !Show_Cost_of_Sale_Group"
          :class="{ 'rotate-90' :! Show_Cost_of_Sale_Group }"
           data-slot="icon" class="inline h-3 w-4  mr-1 fill-slate-500" aria-hidden="true" fill="" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
             Cost Of Sales
          </div>
        <div  class="w-1/2"> {{Format(Cost_Of_Sold_Inventory) }}</div>
      </div>

      <Transition   name="slide-fade">
        <div v-show="Show_Cost_of_Sale_Group" class="  border-black " >

          <div v-for="account in accounts.Net_Purchases.children " class="flex gap-2 my-2 ">
            <div class="w-1/2"> {{ account.name }}</div>
            <div class="w-1/2" > {{format_number(account.balance) }}</div>
          </div>

          <div class="flex gap-2 py-2 ">
            <div class="w-1/2"> Beginning Inventory</div>
            <div class="w-1/2"> {{format_number(accounts.Beginning_Inventory.balance) }}</div>
          </div>

          <div class=" flex gap-2 py-2 ">
            <div class="w-1/2">   Ending Iventory Cost</div>
            <div class="w-1/2"> {{Format(accounts.Ending_Iventory_cost) }}</div>
          </div>
        
        </div>
      </Transition>

      

      <div  :class="{'text-blue-900 to-blue-200 ':Net_trade_STATMENT>0 ,'text-red-900 to-red-200':Net_trade_STATMENT<0}"
       class="flex gap-2 p-2 my-0.5 bg-gradient-to-t from-white text-lg font-extrabold   ">
        <div class="w-1/2 "> Gross Profit</div>
        <div class="w-1/2">{{Format(Net_trade_STATMENT) }} </div>
      </div>
        
    </div>
</template>


<style>
/*
  Enter and leave animations can use different
  durations and timing functions.
*/
.slide-fade-enter-active {
  transition: all 0.6s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.8s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(20px);
  opacity: 0;
}




</style>