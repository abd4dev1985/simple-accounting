<script setup>

import { reactive ,computed,watch,onUpdated,ref,onMounted  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../../searchStore.vue';
import DateObject from '@/DateObject.vue';
import LedgerBook from '@/pages/Account/LedgerBook.vue';

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
  let width= (screen.width>1000)? "80%": "100%" ;
  let height= (screen.width>1000)? "90%": "100%" ;
  console.log(width)
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
  Currency:page.props.currencies[0]
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
  <div ref="Account_Ledger"   class=" m-4">
            
      <h4 class="sticky top-0 z-50 bg-white text-2xl py-3" >LedgerBook  {{ AccountName }} </h4>
      <form  v-if="ShowForm"   @submit.prevent="submit"   >
          <!-- Default Account Input -->
          <div class=" my-3">
              <label class="block text-sm font-semibold text-left" for=""> Account</label>
              <AutoComplete v-model="LedgerBookForm.account" :suggestions="searchStore.available_accounts.value"
                  @complete="searchStore.search_account" optionLabel="name" forceSelection 
                  :pt="{
                      input: {
                      class: 'bg-white h-8 w-44 py-2   dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                      },
                  }">
                  <template #empty>
                      <div   class="font-semibold p-3 border-2 border-blue-500">
                          <div class=""> account <span class="text-blue-600">{{LedgerBookForm.account }}</span> dose not exist </div>
                          <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                      </div>
                  </template> 
              </AutoComplete>
          </div>
          <div v-if="LedgerBookForm.errors.account">{{ LedgerBookForm.errors.account }}</div>
          <div>{{  LedgerBookForm.errors}}</div>

          <!--start DATE INPUT   -->
          <div class="flex-initial ">
                <label class="block text-sm font-semibold text-left" for="">Start Date </label>
                <Calendar v-model="LedgerBookForm.StartDate" showIcon  dateFormat="dd/mm/yy"
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
                <Calendar v-model="LedgerBookForm.EndDate" showIcon  dateFormat="dd/mm/yy"
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
                <AutoComplete v-model="LedgerBookForm.Currency" :suggestions="searchStore.filterd_currencies.value"  
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
    
      <div v-if="FormResult" class="my-5"  >
        <table class="mx-auto w-full table-auto border-collapse border border-slate-400 ">
          <thead  class=" dark:bg-gray-700 bg-white  font-medium dark:border-neutral-500">
                <tr class="sticky top-12 z-10  border  border-slate-400 bg-gray-200  " >
                    <th scope="col " class="w-1/5 p-4 border border-slate-400 ">name</th>
                    <th scope="col" class="w-1/5    p-4 border border-slate-400 "> Debite</th>
                    <th scope="col" class="w-1/5   dark:border-neutral-400 p-4  border border-slate-400">Credite</th>
                    <th scope="col" class="w-1/5  dark:border-neutral-400  p-4 border border-slate-400">Balance</th>
                    <th scope="col" class="w-1/5   dark:border-neutral-400  p-4 border border-slate-400">Date</th>
                </tr>
          </thead>
          <LedgerBook :account="FormResult" >
            
          </LedgerBook>
        </table>
      
      </div>

      
      

  </div>
</div> 
</template>