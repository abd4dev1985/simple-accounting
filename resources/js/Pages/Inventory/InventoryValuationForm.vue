<script setup>

import { reactive ,computed,watch,onMounted,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../../searchStore.vue';
import DateObject from '../../DateObject.vue';
import DataTable from '@/pages/DataTable.vue';
import InventoryValuation from '@/pages/Inventory/InventoryValuation.vue';
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
  let width= (screen.width>1000)? "75%": "100%" ;
  let height= (screen.width>1000)? "90%": "100%" ;
  console.log(width)
  winbox=createWindow({
     mount: inventory_Valuation.value,
     title:'Inventory Ledger ',
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

const LedgerBookForm = useForm({
  product: null,
  StartDate:page.props.year_start ,
  EndDate : new Date() ,
  Currency:page.props.currencies[0],
  winbox_id:null,
})
const currencies= (page.props.currencies)? page.props.currencies:[];

function submit(){
  // winbox.close()
    LedgerBookForm
    .transform((data) => ({
        product: data.product,
        StartDate: DateObject.ToString( data.StartDate )  ,
        EndDate: DateObject.ToString( data.EndDate )    ,
        Currency:data.Currency,
        winbox_id:winbox.id,
    }))
    .post(route('InventoryValuation'),{
      onSuccess: () =>{
         // winbox.close()
         FormResult.value=page.props.inventory_Valuation[winbox.id]
         ShowForm.value=false

      }, 

    })

}





</script>

<template>
<div  class="hidden" >
  <div   class=" m-4" ref="inventory_Valuation" >
              
        <h4  class="m-2 text-2xl" >Inventory Valuation</h4>
        <form   v-if="ShowForm"    @submit.prevent="submit" class="m-3 my-5 flex flex-col justify-between gap-7" >
            <!-- Default Account Input -->
            <div class="w-full">
                <label class="block text-sm font-semibold text-left" for=""> Product</label>
                <AutoComplete v-model="LedgerBookForm.product" :suggestions="searchStore.available_products.value"
                    @complete="searchStore.search_product" optionLabel="name" forceSelection 
                    :pt="{
                        root: {
                          class:'w-full'
                        },
                        input: {
                          class: 'bg-white w-full h-8  py-5 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        },
                    }">
                    <template #empty>
                        <div   class="font-semibold p-3 border-2 border-blue-500">
                            <div class=""> Product <span class="text-blue-600">{{LedgerBookForm.product }}</span> dose not exist </div>
                            <Link :href="searchStore.create_new_product_link.value" class="text-blue-600"> create new one</Link>
                        </div>
                    </template> 
                </AutoComplete>
            </div>

            <!--start DATE INPUT   -->
            <div class="flex-initial w-full">
                  <label class="block text-sm font-semibold text-left" for="">Start Date </label>
                  <Calendar v-model="LedgerBookForm.StartDate" showIcon  dateFormat="dd/mm/yy"
                    :pt="{
                        root:{class:'w-1/2  dark:bg-gray-700'},
                        input: { 
                          class: 'bg-white  h-8 w-full py-5 `dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        },
                        dropdownButton: {
                          root: { class: 'h-8 py-5 bg-sky-700 '}
                        }
                    }"
                  />
            </div>

            <!--END DATE INPUT   -->
            <div class="flex-initial w-full ">
                  <label class="block text-sm font-semibold text-left" for="">End Date </label>
                  <Calendar v-model="LedgerBookForm.EndDate" showIcon  dateFormat="dd/mm/yy"
                    :pt="{
                        root:{class:'w-1/2 dark:bg-gray-700'},
                        input: { 
                          class: 'bg-white  h-8 w-full py-5 `dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        },
                        dropdownButton: {
                          root: { class: 'h-8 py-5 bg-sky-700 '}
                        }
                    }"
                  />
            </div>
            <!-- valuation  CURRENCY INPUT  -->
            <div class=" hidden ">
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
        <!-- result -->
        <div v-if="FormResult"  >
          <InventoryValuation :products="FormResult" />
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