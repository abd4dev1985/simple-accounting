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


let ShowEntries =ref( ( props.level==0 )? true:false )
let ShowChildren =ref(true)

let ToogelShowEntries = ()=>{
    if (props.account.level) {
        ShowEntries.value = !ShowEntries.value
        setTimeout(() => {
            EntrieTableHeader.value.focus()  
        }, 100);
    }

}
let ToogelShowChildren= ()=>{
    if (props.account.children) {
        ShowChildren.value = !ShowChildren.value
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

const EntrieTableHeader= ref(null)
let Last_Raw_In_Entrie_TableHeader = ref(null)

function is_last_raw(element,index,length){
    if (index==length-1) {
        Last_Raw_In_Entrie_TableHeader.value= element
    }
}
 function Show_Orgin_Document_of_Entry(entry_id){
    window.open( route('document.show_by_entry',entry_id) )
 }

onMounted(() => {
    if (Last_Raw_In_Entrie_TableHeader.value && EntrieTableHeader.value) {
       let options = {
            root:null,
            rootMargin: "35% 35% 35% 35%",
            threshold: 1,
        };

        let observer = new IntersectionObserver((entries,observer)=>{
            entries.forEach((entry)=>{
                if (entry.isIntersecting) {
                    console.log(entry.target)
                    console.log(entry)
                    //EntrieTableHeader.value.classList.toggle('bg-blue-300')
                }
            })
        },options)

       observer.observe(Last_Raw_In_Entrie_TableHeader.value)

    }
})


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

     <tr  v-show="!ShowEntries"   tabindex="0" class="border border-slate-400 text-red-900 font-semibold focus:bg-sky-900 focus:text-white divide-x-2 ">
        <td class="py-4 px-1  " >
            <button v-if="account.children" @click="ToogelShowChildren"  type="button" class=" inline"  :style="{marginLeft:account.level-1 +'rem'}"  style="visibility: visible;" tabindex="-1" data-pc-section="rowtoggler" data-pd-ripple="true">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="p-icon p-tree-toggler-icon" aria-hidden="true" data-pc-section="rowtogglericon"><path d="M7.01744 10.398C6.91269 10.3985 6.8089 10.378 6.71215 10.3379C6.61541 10.2977 6.52766 10.2386 6.45405 10.1641L1.13907 4.84913C1.03306 4.69404 0.985221 4.5065 1.00399 4.31958C1.02276 4.13266 1.10693 3.95838 1.24166 3.82747C1.37639 3.69655 1.55301 3.61742 1.74039 3.60402C1.92777 3.59062 2.11386 3.64382 2.26584 3.75424L7.01744 8.47394L11.769 3.75424C11.9189 3.65709 12.097 3.61306 12.2748 3.62921C12.4527 3.64535 12.6199 3.72073 12.7498 3.84328C12.8797 3.96582 12.9647 4.12842 12.9912 4.30502C13.0177 4.48162 12.9841 4.662 12.8958 4.81724L7.58083 10.1322C7.50996 10.2125 7.42344 10.2775 7.32656 10.3232C7.22968 10.3689 7.12449 10.3944 7.01744 10.398Z" fill="currentColor"></path></svg>
            </button>
            <button  v-else type="button" class="p-treetable-toggler p-link inline" :style="{marginLeft:account.level-1 +'rem'}" style=" visibility: hidden;" tabindex="-1" data-pc-section="rowtoggler" data-pd-ripple="true">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="p-icon p-tree-toggler-icon" aria-hidden="true" data-pc-section="rowtogglericon"><path d="M4.38708 13C4.28408 13.0005 4.18203 12.9804 4.08691 12.9409C3.99178 12.9014 3.9055 12.8433 3.83313 12.7701C3.68634 12.6231 3.60388 12.4238 3.60388 12.2161C3.60388 12.0084 3.68634 11.8091 3.83313 11.6622L8.50507 6.99022L3.83313 2.31827C3.69467 2.16968 3.61928 1.97313 3.62287 1.77005C3.62645 1.56698 3.70872 1.37322 3.85234 1.22959C3.99596 1.08597 4.18972 1.00371 4.3928 1.00012C4.59588 0.996539 4.79242 1.07192 4.94102 1.21039L10.1669 6.43628C10.3137 6.58325 10.3962 6.78249 10.3962 6.99022C10.3962 7.19795 10.3137 7.39718 10.1669 7.54416L4.94102 12.7701C4.86865 12.8433 4.78237 12.9014 4.68724 12.9409C4.59212 12.9804 4.49007 13.0005 4.38708 13Z" fill="currentColor"></path></svg>
            </button>
            <span  class ="ml-3 inline " >{{ account.name }}  </span>
            <svg v-if="account.entries" @click="ToogelShowEntries" class="inline ml-1 h-5 w-5 "  data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
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
                <div  v-show="!ShowEntries"   ref="EntrieTableHeader" tabindex="1" class="w-full  flex  bg-white sticky top-24 z-30 text-red-700 focus:bg-sky-900 focus:text-white" >
                    <h1 class="flex-1 p-4 font-semibold  ">
                        <button   type="button" class="p-treetable-toggler p-link" :style="{marginLeft:account.level-1 +'rem'}" style=" visibility: hidden;" tabindex="-1" data-pc-section="rowtoggler" data-pd-ripple="true">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="p-icon p-tree-toggler-icon" aria-hidden="true" data-pc-section="rowtogglericon"><path d="M4.38708 13C4.28408 13.0005 4.18203 12.9804 4.08691 12.9409C3.99178 12.9014 3.9055 12.8433 3.83313 12.7701C3.68634 12.6231 3.60388 12.4238 3.60388 12.2161C3.60388 12.0084 3.68634 11.8091 3.83313 11.6622L8.50507 6.99022L3.83313 2.31827C3.69467 2.16968 3.61928 1.97313 3.62287 1.77005C3.62645 1.56698 3.70872 1.37322 3.85234 1.22959C3.99596 1.08597 4.18972 1.00371 4.3928 1.00012C4.59588 0.996539 4.79242 1.07192 4.94102 1.21039L10.1669 6.43628C10.3137 6.58325 10.3962 6.78249 10.3962 6.99022C10.3962 7.19795 10.3137 7.39718 10.1669 7.54416L4.94102 12.7701C4.86865 12.8433 4.78237 12.9014 4.68724 12.9409C4.59212 12.9804 4.49007 13.0005 4.38708 13Z" fill="currentColor"></path></svg>
                        </button>
                        <span  class="ml-3" >{{ account.name }} </span> 
                        <svg  v-if="account.entries" @click="ToogelShowEntries"  class="inline ml-1 w-5 h-5" data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </h1>
                    <h1 class="flex-1 p-4 font-semibold ">  {{ Format (total_balance.total_debit) }}   </h1>
                    <h1 class="flex-1 p-4 font-semibold ">  {{ Format(total_balance.total_credit ) }}   </h1>
                    <h1 class="flex-1 p-4 font-semibold ">  {{Format(total_balance.total_debit - total_balance.total_credit )  }}   </h1>
                    <h1 class="flex-1 p-4 font-semibold ">   </h1>

                    <h1 class="flex-1 p-4 font-semibold ">    </h1>

                </div>
                

                <table     class="border-collapse  table-fixed w-full overflow-visible divide-y-2">
                    <thead   class=" dark:bg-gray-700 font-medium dark:border-neutral-500  fi">
                        <tr   class="bg-gray-300 sticky top-36 z-30 " >
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
                        
                        <td  :ref="(el)=>{is_last_raw(el,index,account.entries.length)}" class="p-4 text-sm">{{ entry.entry_id }}</td>
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
    <LedgerBook v-if="ShowChildren" v-for="childe in account.children" :key="childe.id" :account="childe" :level="level+1" >
            
    </LedgerBook>
  
    
    
   

  

</template>