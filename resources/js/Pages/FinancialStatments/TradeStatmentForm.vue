<script setup>

import { reactive ,computed,watch,onMounted,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../../searchStore.vue';
import DateObject from '../../DateObject.vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import TradeStatment from '@/pages/FinancialStatments/TradeStatment.vue';
import TrialBalance from '../TrialBalance.vue';


import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import { useWinBox } from 'vue-winbox'


const toast = useToast();
let props =defineProps({
  index:{   } ,

})
const createWindow = useWinBox()
let winbox ;
let inventory_Valuation =ref()

onMounted(() => {
  let width= (screen.width>1000)? "50%": "100%"  ;
  let height= (screen.width>1000)? "95%": "100%" ;
  winbox=createWindow({
     mount: inventory_Valuation.value,
     title:'Trade Statment ',
     index:40,
     class:'bg-sky-600',
     width: width , height: height ,
     x: "center", y: "center",

  })
})
let ShowForm= ref(true)
let FormResult= ref()

let severity_style= ref('');
//define computed props
const page = usePage()

const Form = useForm({
  StartDate:page.props.year_start ,
  EndDate : new Date() ,
  Currency:page.props.currencies[0],
  winbox_id:null,
})
const currencies= (page.props.currencies)? page.props.currencies:[];

function submit(){
  // winbox.close()
    Form
    .transform((data) => ({
        StartDate: DateObject.ToString( data.StartDate )  ,
        EndDate: DateObject.ToString( data.EndDate )    ,
        Currency:data.Currency,
        winbox_id:winbox.id,
    }))
    .post(route('TradeStatment'),{
      onSuccess: () =>{
         // winbox.close()
         FormResult.value=page.props.Trade_Statment[winbox.id]
         ShowForm.value=false

      }, 

    })

}





</script>

<template>
<div  class="hidden" >
  <div   class=" m-4" ref="inventory_Valuation" >
              
        <h4  class="my-5 text-2xl" >Trade Statment</h4>
        <form   v-if="ShowForm"    @submit.prevent="submit" class=" my-1 flex flex-col justify-between gap-5"  >
            <!--start DATE INPUT   -->
            <div class="flex-initial w-full">
                  <label class="block text-sm font-semibold text-left" for="">Start Date </label>
                  <Calendar v-model="Form.StartDate" showIcon  dateFormat="dd/mm/yy"
                    :pt="{
                        root:{class:'w-1/2 dark:bg-gray-700'},
                        input: { 
                          class: 'bg-white  h-8 w-full py-5 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        },
                        dropdownButton: {
                          root: { class:'h-8 py-5 bg-sky-700 '}
                        }
                    }"
                  />
            </div>
          
            <!--END DATE INPUT   -->
            <div class="flex-initial w-full ">
                  <label class="block text-sm font-semibold text-left" for="">End Date </label>
                  <Calendar v-model="Form.EndDate" showIcon  dateFormat="dd/mm/yy"
                    :pt="{
                        root:{class:' w-1/2 dark:bg-gray-700'},
                        input: { 
                          class: 'bg-white h-8 w-full py-5 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        },
                        dropdownButton: {
                          root: { class:'h-8 py-5 bg-sky-700 '}
                        }
                    }"
                  />
            </div>
            <!-- valuation  CURRENCY INPUT  -->
            <div class=" ">
                  <label class="block text-sm font-semibold text-left " for="">
                    Currency
                  </label>
                  <AutoComplete v-model="Form.Currency" :suggestions="searchStore.filterd_currencies.value"  
                    @complete="searchStore.search_currencey" optionLabel="name" forceSelection
                    :pt="{
                        input: {
                          class: 'bg-white h-8 w-24 py-2 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        },
                      }">
                      <template #empty>
                          <div   class="font-semibold p-3 border-2 border-blue-500">
                              <div class=""> currency <span class="text-blue-600">{{Invoice_Currency }}</span> dose not exist </div>
                              <Link :href="searchStore.create_new_currencey_link.value" class="text-blue-600"> create new one</Link>
                          </div>
                      </template> 
                  </AutoComplete>
                </div>

            <!-- submit -->
            <button class="flex w-max p-2 bg-sky-800 text-white font-semibold rounded-lg gap-1 " type="submit" >
              <svg class="h-7 w-7" data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
              <span class="text-lg">Show result </span>    
            </button>

        </form>
        <!-- result -->
        <div v-if="FormResult" class="" >
            <div class="text-gray-900 flex flex-wrap justify-start gap-6  text-sm">
              <span class=""> From :  {{ Form.StartDate }}  </span>
              <span class=""> Untill : {{  DateObject.ToString( Form.EndDate ) }} </span>
              <span class=""> Currency : {{ Form.Currency.name }} </span>

            </div>

            <TradeStatment :accounts="FormResult" >

            </TradeStatment>
        </div>
        

        
        <div  class="clear-both"></div>
        <Toast 
            :pt="{ 
                root:{class: 'opacity-95'},
                content: { class:severity_style ,},
                icon:{class: 'stroke-white fill-white'},
            }"
        />

  </div>
</div>
</template>