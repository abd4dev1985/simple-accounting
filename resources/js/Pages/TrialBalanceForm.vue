<script setup>

import { reactive ,computed,watch,onMounted,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../searchStore.vue';
import DateObject from '../DateObject.vue';
import DataTable from '@/pages/DataTable.vue';
import TrialBalance from '@/pages/TrialBalance.vue';


import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import ccc from '@/Components/ccc.vue';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import { useWinBox } from 'vue-winbox'


const toast = useToast();
let props =defineProps({
  index:{   } ,

})
const createWindow = useWinBox()
let winbox ;
let inventory_ledger =ref()

onMounted(() => {
  let width= (screen.width>1000)? "75%": "100%" ;
  let height= (screen.width>1000)? "90%": "100%" ;
  console.log(width)
  winbox=createWindow({
     mount: inventory_ledger.value,
     title:'trial balance',
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

const TrialbbalanceForm = useForm({
  account: null,
  StartDate:page.props.year_start ,
  EndDate : new Date() ,
  Currency:page.props.currencies[0],
  winbox_id:null,
})
const currencies= (page.props.currencies)? page.props.currencies:[];

function submit(){
  // winbox.close()
    TrialbbalanceForm
    .transform((data) => ({
        account: data.account,
        StartDate: DateObject.ToString( data.StartDate )  ,
        EndDate: DateObject.ToString( data.EndDate )    ,
        Currency:data.Currency,
        winbox_id:winbox.id,
    }))
    .post(route('accounts.TrialBalance'),{
      onSuccess: () =>{
         // winbox.close()
         FormResult.value=page.props.tial_balance[winbox.id]
         ShowForm.value=false

      }, 

    })

}





</script>

<template>
<div  class="hidden" >
  <div   class=" m-4" ref="inventory_ledger" >
              
        <h4  class="m-2 text-2xl" >Trial Balance </h4>
        <form   v-if="ShowForm"    @submit.prevent="submit"   >
            <!-- Default Account Input -->
            <div class=" my-5">
                <label class="block text-sm font-semibold text-left" for=""> Account</label>
                <AutoComplete v-model="TrialbbalanceForm.account" :suggestions="searchStore.available_accounts.value"
                    @complete="searchStore.search_account" optionLabel="name" forceSelection 
                    :pt="{
                        input: {
                        class: 'bg-white h-8 w-44 py-2   dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        placeholder:'All accounts',
                        },
                    }">
                    <template #empty>
                        <div   class="font-semibold p-3 border-2 border-blue-500">
                            <div class=""> account <span class="text-blue-600">{{TrialbbalanceForm.account }}</span> dose not exist </div>
                            <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                        </div>
                    </template> 
                </AutoComplete>
            </div>
            <div>{{  TrialbbalanceForm.errors}}</div>

            <!--start DATE INPUT   -->
            <div class="flex-initial ">
                  <label class="block text-sm font-semibold text-left" for="">Start Date </label>
                  <Calendar v-model="TrialbbalanceForm.StartDate" showIcon  dateFormat="dd/mm/yy"
                    :pt="{
                        root:{class:' dark:bg-gray-700'},
                        input: { 
                          class: 'bg-white text-center h-8 w-32 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        },
                        dropdownButton: {
                          root: { class: 'h-8' }
                        }
                    }"
                  />
            </div>

            <!--END DATE INPUT   -->
            <div class="flex-initial ">
                  <label class="block text-sm font-semibold text-left" for="">End Date </label>
                  <Calendar v-model="TrialbbalanceForm.EndDate" showIcon  dateFormat="dd/mm/yy"
                    :pt="{
                        root:{class:' dark:bg-gray-700'},
                        input: { 
                          class: 'bg-white text-center h-8 w-32 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        },
                        dropdownButton: {
                          root: { class: 'h-8' }
                        }
                    }"
                  />
            </div>
            <!-- INVOICE CURRENCY INPUT  -->
            <div class=" ">
                  <label class="block text-sm font-semibold text-left " for="">
                    Currency
                  </label>
                  <AutoComplete v-model="TrialbbalanceForm.Currency" :suggestions="searchStore.filterd_currencies.value"  
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
            <button type="submit" >ok</button>
        </form>
        <!-- result -->
        <div v-if="FormResult"  >
          <TrialBalance :accounts="FormResult"  >
            
          </TrialBalance>

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