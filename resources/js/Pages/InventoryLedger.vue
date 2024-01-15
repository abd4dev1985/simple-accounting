<script setup>

import { reactive ,computed,watch,onUpdated,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../searchStore.vue';
import DateObject from '../DateObject.vue';

import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import ccc from '@/Components/ccc.vue';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";

const toast = useToast();

let severity_style= ref('');
//define computed props
const page = usePage()

const LedgerBookForm = useForm({
  product: null,
  StartDate:page.props.year_start ,
  EndDate : new Date() ,
  Currency:page.props.currencies[0]
})
const currencies= (page.props.currencies)? page.props.currencies:[];

function submit(){
    LedgerBookForm
    .transform((data) => ({
        product: data.product,
        StartDate: DateObject.ToString( data.StartDate )  ,
        EndDate: DateObject.ToString( data.EndDate )    ,
        Currency:data.Currency

    }))
    .post(route('products.ledgerBook'))

}



</script>

<template>
  
<div   class=" m-4">
          
    <h4 class="text-2xl" >Inventory LEDGER</h4>
    <form @submit.prevent="submit"   >
        <!-- Default Account Input -->
        <div class=" my-5">
            <label class="block text-sm font-semibold text-left" for=""> Product</label>
            <AutoComplete v-model="LedgerBookForm.product" :suggestions="searchStore.available_products.value"
                @complete="searchStore.search_product" optionLabel="name" forceSelection 
                :pt="{
                    input: {
                    class: 'bg-white h-8 w-44 py-2   dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
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
   
    

    
    <div   class="clear-both"></div>
    <Toast 
        :pt="{ 
            root:{class: 'opacity-95'},
            content: { class:severity_style ,},
            icon:{class: 'stroke-white fill-white'},
        }"
    />

</div>
</template>