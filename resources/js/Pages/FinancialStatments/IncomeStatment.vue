<script setup>

import { reactive ,computed,watch,onMounted,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import AppLayout from '@/Layouts/AppLayout.vue';
import TradeStatment from '@/pages/FinancialStatments/TradeStatment.vue';


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

// format number and make more readable for human by addind comma  
function Format(value){
    let formatter =Intl.NumberFormat('en')
    if (  !isNaN(Number(value))  &&  Number(value) != 0    ) {
        return formatter.format(value)
    }else{  
        return 0 
    }
}



const dt = ref();
const exportCSV = () => { dt.value.exportCSV()}



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
let ShowRevenuesGroup = ref(false)
let ShowExpensesGroup = ref(false)



</script>

<template>

    <div class="" >
      <!-- TradeStatment --> 
      <TradeStatment :accounts="accounts" >
      </TradeStatment>

      <!-- Expenses --> 
      <div class="flex gap-2 font-semibold bg-gray-200 py-2 my-0.5 ">
        <div class="w-1/2">
          <svg  @click="ShowExpensesGroup = !ShowExpensesGroup"
          :class="{ 'rotate-90' :! ShowExpensesGroup }"
           data-slot="icon" class="inline h-3 w-4  mr-1 fill-slate-500" aria-hidden="true" fill="" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
            Expenses
          </div>
        <div  class="w-1/2"> {{Format(accounts.Expenses.balance) }}</div>
      </div>

      <Transition   name="slide-fade">
        <div v-show="ShowExpensesGroup" class="  border-black " >
          <div v-for="account in accounts.Expenses.children " class="flex gap-2 my-2 ">
            <div class="w-1/2 px-2"> {{ account.name }}</div>
            <div class="w-1/2" > {{format_number(account.balance) }}</div>
          </div>
        </div>
      </Transition>

      <!-- Revenues --> 
      <div class="flex gap-2 font-semibold bg-gray-200 py-2  my-0.5">
        <div class="w-1/2">
          <svg  @click="ShowRevenuesGroup = !ShowRevenuesGroup"
          :class="{ 'rotate-90' :! ShowExpensesGroup }"
           data-slot="icon" class="inline h-3 w-4  mr-1 fill-slate-500" aria-hidden="true" fill="" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
            Revenues
          </div>
        <div  class="w-1/2"> {{Format(accounts.Revenues.balance) }}</div>
      </div>

      <Transition   name="slide-fade">
        <div v-show="ShowRevenuesGroup" class="  border-black " >
          <div v-for="account in accounts.Revenues.children " class="flex gap-2 my-2 ">
            <div class="w-1/2 px-2"> {{ account.name }}</div>
            <div class="w-1/2" > {{format_number(account.balance) }}</div>
          </div>
        </div>
      </Transition>


      <div :class="{'text-blue-900 to-blue-200 ':accounts.Net_Profit>0 ,'text-red-900 to-red-200':accounts.Net_Profit<0}"
       class="flex gap-2 p-2 my-0.5 bg-gradient-to-t from-white  text-lg font-extrabold  ">
        <div class="w-1/2 "> Net Income</div>
        <div class="w-1/2">{{Format(accounts.Net_Profit) }} </div>
      </div>


      
      



        
    </div>


</template>