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
  level:{   default:0 },

})


let ShowEntries =ref(false)


let ToogelShowEntries= ()=>{
    if (props.account.children||props.account.entries ) {
        ShowEntries.value = !ShowEntries.value
       
    }
}

function get_final_balance(account){
    let total_balance_object = 
    { 
        total_debit:(account.PreviousBalance && account.PreviousBalance>0 )? Number(account.PreviousBalance):0,
        total_credit : (account.PreviousBalance && account.PreviousBalance<0 )? Number(account.PreviousBalance):0,
    }
    if (account.entries) {
        account.entries.forEach( (entry,index)=>{
            total_balance_object.total_debit +=  Number(entry.debit_amount)
            total_balance_object.total_credit +=  Number(entry.credit_amount)
        }) 
    }
    if (account.children) {
        total_balance_object = account.children.reduce((accmulation,childe)=>{
            accmulation.total_debit+= get_final_balance(childe).total_debit
            accmulation.total_credit+= get_final_balance(childe).total_credit
            return accmulation 
        },total_balance_object) 
    }
    return total_balance_object
}
 
 let total_balance = computed(()=>{
    return get_final_balance(props.account)
 } )  

// format number and make more readable for human by addind comma  
function Format(value){
    let formatter =Intl.NumberFormat('en')
    if (  !isNaN(Number(value))  &&  Number(value) != 0    ) {
        return formatter.format(value)
    }else{  
        return 0 
    }
}

let Balances = computed(() => {
    let Array =[]
    let FirstBalance = (props.account.PreviousBalance)?Number(props.account.PreviousBalance):0
    if ( props.account.entries) {
        props.account.entries.forEach((entry,index)=>{
            let Pervious_Balance =(index>0)? Array[index-1]: FirstBalance
            Array.push(Number( Pervious_Balance) + Number(entry.debit_amount) - Number(entry.credit_amount) )
        }) 
    }
    return Array
})


//let Last_Raw_In_Entrie_TableHeader = ref(null)


 function Show_Orgin_Document_of_Entry(entry_id){
    window.open( route('document.show_by_entry',entry_id) )
 }



const dt = ref();
const exportCSV = () => { dt.value.exportCSV()}

let severity_style= ref('');
//define computed props
const page = usePage()
const currencies= (page.props.currencies)? page.props.currencies:[];

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.CONTAINS },
   
});
let MarginLeft = 'ml-'+props.account.level*3

</script>

<template>

     <tr tabindex="0" class="sticky top-24 z-40   border border-slate-400 text-red-900 font-semibold focus:bg-sky-900 focus:text-white divide-x-2 ">
        <td class="py-4 px-1 relative  " >
            <svg v-if="account.entries" @click="ToogelShowEntries" class="inline absolute left-1 top-5 h-5 w-5 "  data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <button  type="button" class="p-treetable-toggler p-link inline" :style="{marginLeft:account.level-1 +'rem'}" style=" visibility: hidden;" tabindex="-1" data-pc-section="rowtoggler" data-pd-ripple="true">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="p-icon p-tree-toggler-icon" aria-hidden="true" data-pc-section="rowtogglericon"><path d="M4.38708 13C4.28408 13.0005 4.18203 12.9804 4.08691 12.9409C3.99178 12.9014 3.9055 12.8433 3.83313 12.7701C3.68634 12.6231 3.60388 12.4238 3.60388 12.2161C3.60388 12.0084 3.68634 11.8091 3.83313 11.6622L8.50507 6.99022L3.83313 2.31827C3.69467 2.16968 3.61928 1.97313 3.62287 1.77005C3.62645 1.56698 3.70872 1.37322 3.85234 1.22959C3.99596 1.08597 4.18972 1.00371 4.3928 1.00012C4.59588 0.996539 4.79242 1.07192 4.94102 1.21039L10.1669 6.43628C10.3137 6.58325 10.3962 6.78249 10.3962 6.99022C10.3962 7.19795 10.3137 7.39718 10.1669 7.54416L4.94102 12.7701C4.86865 12.8433 4.78237 12.9014 4.68724 12.9409C4.59212 12.9804 4.49007 13.0005 4.38708 13Z" fill="currentColor"></path></svg>
            </button>
            <span  class ="ml-3 inline " >{{ account.name }}  </span>
        </td>
        <td class="p-4" > {{  Format (total_balance.total_debit) }}    </td>
        <td  class="p-4">  {{ Format(total_balance.total_credit ) }}      </td>
        <td class="p-4"> {{ Format(total_balance.total_debit - total_balance.total_credit ) }} </td>
        <td class="p-4">    </td>
    </tr>
    
    <tr v-if="account.entries" class=" " >
        <td v-show="ShowEntries" colspan="6">
            <div  class="w-full">
                <!--  entries table  -->
                <table     class="border-collapse  table-fixed w-full overflow-visible divide-y-2">
                    <thead   class=" dark:bg-gray-700 font-medium dark:border-neutral-500 sticky top-36 z-40">
                        <tr   class="bg-gray-300" >
                            <th scope="col " class=" p-4 ">Entry id</th>
                            <th scope="col" class="   p-4 "> Debite</th>
                            <th scope="col" class="   p-4 ">Credite</th>
                            <th scope="col" class="   p-4">Balance</th>
                            <th scope="col" class="   p-4 ">Description</th>
                            <th scope="col " class="  p-4   ">Date</th>
                        </tr>
                    </thead>
                    <tr v-if="account.PreviousBalance" class="bg-gray-50 focus:bg-sky-900 focus:text-white divide-x-2 " tabindex="0" >
                        
                        <td class="p-4 text-sm"></td>
                        <td class="p-3 text-sm" ></td>
                        <td class="p-3 text-sm" ></td>
                        <td  class="p-3 text-sm" >{{ Format(account.PreviousBalance ) }}</td>
                        <td class="p-3 text-sm" >Previous Balances</td>
                        <td class="p-3 text-sm"  >__</td>
                    </tr>
                    <tr v-for="(entry,index) in account.entries" :key="entry.id" @dblclick="Show_Orgin_Document_of_Entry(entry.entry_id )"
                     class="bg-gray-50 focus:bg-sky-900 focus:text-white divide-x-2 " tabindex="0" >
                        
                        <td   class="p-4 text-sm">{{ entry.entry_id }}</td>
                        <td class="p-3 text-sm" >{{ Format(entry.debit_amount) }}</td>
                        <td class="p-3 text-sm" >{{Format(entry.credit_amount ) }}</td>
                        <td  class="p-3 text-sm" >{{ Format(Balances[index]) }}</td>
                        <td class="p-3 text-sm" >{{ entry.description  }}</td>
                        <td class="p-3 text-sm"  >{{ entry.date }}</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
    <LedgerBook2 v-if="account.children" v-for="childe in account.children" :key="childe.id" :account="childe" :level="level+1" >
            
    </LedgerBook2>
  
    
    
   

  

</template>