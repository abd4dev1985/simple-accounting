<script setup>

import { reactive ,computed,watch,onUpdated,ref,  } from 'vue'
import {Link,usePage} from '@inertiajs/vue3';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../../searchStore.vue';
import ccc from '@/Components/ccc.vue';


const entries =  defineModel('entries');

let props= defineProps({
  customfields:{ default:[]}
})

//define computed props
const page = usePage()

let form_have_been_adjusted = ref(false);

const rows = ref([])
const scrollable_table = ref()
const tableHeader=ref()

let TableObject =computed(()=>{
  return {
    Table:scrollable_table.value ,TableHeader:tableHeader.value,Rows:rows.value,
    CollumnsCount:props.columns_count + props.customfields.length}
})

//inforce value of an element inside array to be a number  
function Force_Number_VALUE(array_objects,object_key ,index){
  if (isNaN(Number(array_objects[index][object_key])) && Number (array_objects[index][object_key]) !=null ) {
    array_objects[index][object_key] =null
  }
}
//inforce value of an element inside array to be a oject  
function Force_Object_VALUE(array_objects,object_key ,index){
  if (isNaN(Number(array_objects[index][object_key])) && Number (array_objects[index][object_key]) !=null ) {
    array_objects[index][object_key] =null
  }
}

//clear credit_amount input value if valide debit_amount input is inserted in the same line of entry
function remove_credit_amount(index) {
  Force_Number_VALUE(entries.value,'debit_amount',index)
  if (entries.value[index].debit_amount  ) {
    entries.value[index].credit_amount = null 
  }
}
// clear debit_amount input value if valide credit_amount input is inserted in the same line of entry
function remove_debit_amount(index) {
  Force_Number_VALUE  (entries.value,'credit_amount',index)
  if (entries.value[index].credit_amount) {
    entries.value[index].debit_amount =null
  }
}

</script>

<template>
  <!-- entry table   -->
  <div ref="scrollable_table"  class=" lg:h-[410px] mx-6 relative  overflow-auto scrollbar larg:max-w-[73vw] " >
      <table class=" dark:text-gray-200   text-center border-separate   text-sm font-light">
        <thead ref="tableHeader"  class="sticky top-0 z-[20] bg-white dark:bg-gray-700 bg-inherit  border-2 font-medium dark:border-neutral-400">
            <tr class="" >
            <th scope="col " class=" border-r b py-4 sticky  z-[10] left-0   bg-gray-200 ">#</th>
            <th scope="col" class=" border-r border-b  py-4  "> Debite</th>
            <th scope="col" class="border-r border-b  dark:border-neutral-400 py-4  ">Credite</th>
            <th scope="col" class=" border-r border-b  dark:border-neutral-400  py-4">Account</th>
            <th scope="col" class=" block border-r border-b  overflow-auto resize-x py-4 min-w-[200px] ">Description</th>
            <th scope="col" class="border-r border-b  dark:border-neutral-400 py-4">Cost center</th>
            <th scope="col" class="border-r border-b  dark:border-neutral-400 py-4">Currency</th>
            <th scope="col" class=" border-r border-b  dark:border-neutral-400 py-4">rate</th>
            <th v-for="(field,index) in customfields" :key="index"  scope="col" class="py-4  dark:border-neutral-400">
              <div class=" min-w-[100px] overflow-auto resize-x">  {{ field }} </div>
            </th>
            </tr>
        </thead>
        <tbody> 
            <tr v-for="(i,index) in entries.length " :key="index" ref="rows" class=" odd:bg-white even:bg-slate-200 dark:border-neutral-500 dark:odd:bg-gray-800 dark:even:bg-gray-700  ">
              <td class="sticky left-0 bg-inherit z-10  text-center font-medium border-r  border-gray-400">
                  <div class=" w-full py-3 px-1  border-gray-400 ">{{index+1}}</div>                    
              </td>

              <td class="whitespace-nowrap   border-gray-400 ">
                <ccc  v-model="entries[index].debit_amount"   @change="remove_credit_amount(index)"  
                :TableObject="TableObject"  :rows_index="index" :columns_index=1  Format="number" />
              </td>

              <td class="whitespace-nowrap   border-gray-400 ">
                <ccc  v-model="entries[index].credit_amount"   @change="remove_debit_amount(index)" 
                :TableObject="TableObject"  :rows_index="index" :columns_index=2  Format="number" />                                 
              </td>
              
              <td class="whitespace-nowrap  border-gray-400   ">                         
                <ccc v-model="entries[index].account"   @change="form_have_been_adjusted=true" :TableObject="TableObject"  :rows_index="index" :columns_index=3
                Format="aoutcomplete" :SearchFunction="searchStore.search_account" :Suggestions="searchStore.available_accounts.value"  >  
                  <template #emptySuggestions>
                    <div class=""> account <span class="text-blue-600">{{entries[index].account }}</span> dose not exist </div>
                    <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                  </template>
                </ccc>
              </td>

              <td class="whitespace-nowrap border-gray-400  ">
                <ccc  v-model="entries[index].description"  @change="form_have_been_adjusted=true" 
                :TableObject="TableObject"  :rows_index="index" :columns_index=4  Format="text" />
              </td>

              <td class="whitespace-nowrap border-r border-gray-400">
                <ccc v-model="entries[index].cost_center" :TableObject="TableObject"  :rows_index="index" :columns_index=5
                Format="aoutcomplete" :SearchFunction="searchStore.search_cost_center" 
                :Suggestions="searchStore.available_cost_centers.value" >
                  <template #emptySuggestions>
                    <div class=""> cost center <span class="text-blue-600">{{entries[index].cost_center }}</span> dose not exist </div>
                    <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                  </template>
                </ccc>
              </td>

              <td  :class="{'text-transparent':!entries[index].debit_amount}"  class="whitespace-nowrap  ">
                {{ !entries[index].debit_amount }}
                <ccc v-model="entries[index].currency" :TableObject="TableObject" :rows_index="index" :columns_index=6
                @UpdateCurrencyRate="(rate)=>entries[index].currency_rate=rate" 
                Format="aoutcomplete" :SearchFunction="searchStore.search_currencey" :Suggestions="searchStore.filterd_currencies.value" >  
                  <template #emptySuggestions>
                    <div class=""> currency <span class="text-blue-600">{{entries[index].currency }}</span> dose not exist </div>
                  </template>
                </ccc>
              </td>

              <td :class="{'text-transparent': entries[index].currency_rate==1}" class="whitespace-nowrap    " >
                <ccc v-model="entries[index].currency_rate"  :TableObject="TableObject"  :rows_index="index" :columns_index=7
                :Default ="entries[index].currency?.default_rate"  Format="number" 
                />
              </td>

              <td v-for="(field,failed_index) in customfields" :key="failed_index" class="whitespace-nowrap ">
                <ccc  v-model="entries[index].customfields[field]" 
                :TableObject="TableObject"  :rows_index="index" :columns_index=8  Format="text" />
              </td>

              <td class="whitespace-nowrap border border-gray-400">
              </td>
            </tr>
        </tbody>
      </table>
  </div>
               
</template>