<script setup>

import { reactive ,computed,watch,onUpdated,ref,onMounted  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../../searchStore.vue';
import DateObject from '@/DateObject.vue';
import LedgerBook from '@/pages/Account/LedgerBook.vue';
import ToggleButton from 'primevue/togglebutton';
// import ToggleSwitch from 'primevue/toggleswitch';


import { useWinBox } from 'vue-winbox'

const createWindow = useWinBox()
let winbox ;

let props =defineProps({
    account:{   } ,
})

let FormResult= ref()
let MY_Balances= ref([])
let ShowForm= ref(true)

let Account_Ledger= ref()

let AccountName= computed(()=>{
    return FormResult.value?.name
})

onMounted(() => {
  let width= (screen.width>1000)? "50%": "100%" ;
  let height= (screen.width>1000)? "95%": "100%" ;
  winbox=createWindow({
     mount: Account_Ledger.value,
     title:'Account ledger',
     index:40,
     class:'bg-sky-600',
     width: width , height: height ,
     x: "center", y: "center",

  })
})

let severity_style= ref('');
//define computed props
const page = usePage()

const LedgerBookForm = useForm({
  account: props.account,
  StartDate:page.props.year_start ,
  EndDate : new Date() ,
  Currency:page.props.currencies[0],
  Show_Previous_Balance:true,
})
const currencies= (page.props.currencies)? page.props.currencies:[];

function submit(){
    LedgerBookForm
      .transform((data) => ({
          account: data.account,
          StartDate: DateObject.ToString( data.StartDate ) ,
          EndDate: DateObject.ToString( data.EndDate ) ,
          Currency:data.Currency  ,
          winbox_id:winbox.id ,
      }))
      .post(route('accounts.ledgerBook'),
      {
        onSuccess: () =>{
          FormResult.value = page.props.Account_Ledger_Book[winbox.id]
          ShowForm.value = false
          winbox.fullscreen()
          winbox.setTitle(  AccountName.value +" Ledger Book")
        }
      },

        
    
    )

}


</script>

<template>
 <div class="hidden"  >
  <div ref="Account_Ledger"   class=" mx-4">
            
      <h4 class="sticky top-0 z-50 bg-white text-2xl py-3" >Ledger Book of {{ AccountName }} </h4>
      <form  v-if="ShowForm"   @submit.prevent="submit" class=" my-1 flex flex-col justify-between gap-5"   >
          <!-- Default Account Input -->
          <div class="  w-full">
              <label class="block text-sm font-semibold text-left" for=""> Account</label>
              <div v-if="LedgerBookForm.errors.account" class="block text-sm font-semibold text-left text-red-600"  >
                  {{  LedgerBookForm.errors.account}}
              </div>
              <AutoComplete v-model="LedgerBookForm.account" :suggestions="searchStore.available_accounts.value"
                  @complete="searchStore.search_account" optionLabel="name"   forceSelection 
                  @item-select="LedgerBookForm.clearErrors('account')" 
                  :pt="{
                      root: {
                        class:'w-full'
                      },
                      input: {
                      class: 'bg-white w-full h-8  py-5 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                      },
                  }">
                  <template #option="slotProps">
                    <div>{{ slotProps.option.number }}_ {{ slotProps.option.name }}</div>
                  </template>
                  <template #empty>
                      <div   class="font-semibold p-3 border-2 border-blue-500">
                          <div class=""> account <span class="text-blue-600">{{LedgerBookForm.account }}</span> dose not exist </div>
                          <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                      </div>
                  </template> 
              </AutoComplete>
          </div>
          

          <!--start DATE INPUT   -->
          <div class="flex-initial w-full  ">
                <label class="block text-sm font-semibold text-left" for="">Start Date </label>
                <div v-if="LedgerBookForm.errors.StartDate" class="block text-sm font-semibold text-red-600"  >
                    {{  LedgerBookForm.errors.StartDate}}
                </div>
                <Calendar v-model="LedgerBookForm.StartDate" showIcon  dateFormat="dd/mm/yy"
                @date-select="LedgerBookForm.clearErrors('StartDate')"
                  :pt="{
                      root:{class:' w-1/2 dark:bg-gray-700'},
                      input: { 
                        class:'bg-white  h-8 w-full py-5 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                      },
                      dropdownButton: {
                        root: { class:'h-8 py-5 bg-sky-700 '}
                      }
                  }"
                />
          </div>

          <!--END DATE INPUT   -->
          <div class="flex-initial  w-full ">
                <label class="block text-sm font-semibold text-left" for="">End Date </label>
                <div v-if="LedgerBookForm.errors.EndDate" class="block text-sm font-semibold text-red-600"  >
                    {{  LedgerBookForm.errors.EndDate}}
                </div>
                <Calendar v-model="LedgerBookForm.EndDate" showIcon  dateFormat="dd/mm/yy"
                @date-select="LedgerBookForm.clearErrors('EndDate')"
                  :pt="{
                      root:{class:' w-1/2 dark:bg-gray-700'},
                      input: { 
                        class:'bg-white  h-8 w-full py-5 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                      },
                      dropdownButton: {
                        root: { class:'h-8 py-5 bg-sky-700 '},
                      }
                  }"
                />
          </div>
           <!-- INVOICE CURRENCY INPUT && Show Previous Balance  INPUT  -->
          <div class="flex justify-between">
            <!-- INVOICE CURRENCY INPUT  -->
            <div class=" ">
              <label class="block text-sm font-semibold text-left " for="">
                Currency
              </label>
              <AutoComplete v-model="LedgerBookForm.Currency" :suggestions="searchStore.filterd_currencies.value"  
                @complete="searchStore.search_currencey" optionLabel="name" forceSelection
                :pt="{
                    input: {
                      class: 'bg-white h-8 w-24  dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
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
      

          </div>

          <!-- Previous Balance  INPUT  -->
          <div>
              <div class=" text-sm font-semibold text-gray-900 dark:text-gray-300">
                Show Previous Balance
              </div>
              <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="LedgerBookForm.Show_Previous_Balance" class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-sky-700"></div>
              </label>
          </div>

            
          <!-- submit -->
          <button class="flex w-max p-2 bg-sky-800 text-white font-semibold rounded-lg gap-1 " type="submit" >
            <svg class="h-7 w-7" data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span class="text-lg">Show result </span>    
          </button>
          
      </form>
    
      <div v-if="FormResult" class="my-5"  >
        <div class="my-2 font-semibold text-gray-900 flex flex-wrap justify-start gap-6  text-sm">
            <span class=""> From :  {{ LedgerBookForm.StartDate }}  </span>
            <span class=""> Untill : {{  DateObject.ToString( LedgerBookForm.EndDate ) }} </span>
            <span class=""> Currency : {{ LedgerBookForm.Currency.name }} </span>
        </div>

        <table class="mx-auto w-full min-w-[700px] table-fixed border-collapse border border-slate-400 ">
          <thead  class=" dark:bg-gray-700 bg-white  font-medium dark:border-neutral-500">
                <tr class="sticky top-12 z-30  border  border-slate-400 bg-gray-200  " >
                    <th scope="col " class=" p-4 border border-slate-400  ">name</th>
                    <th scope="col" class="   p-4 border border-slate-400 ">Total Debite</th>
                    <th scope="col" class="  dark:border-neutral-400 p-4  border border-slate-400">Total Credite</th>
                    <th scope="col" class="  dark:border-neutral-400  p-4 border border-slate-400">Balance</th>
                </tr>
          </thead>
          <LedgerBook :account="FormResult" >
            
          </LedgerBook>
        </table>
      
      </div>

      
      

  </div>
</div> 
</template>