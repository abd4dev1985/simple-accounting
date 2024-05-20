<script setup>

import { reactive ,computed,watch,onMounted,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../../searchStore.vue';
import DateObject from '../../DateObject.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';   
import Row from 'primevue/row';                   
import { FilterMatchMode } from 'primevue/api';
import Button from 'primevue/button';


let props =defineProps({
  ProductCount:{   } ,

})


const dt = ref();
const exportCSV = () => {  dt.value.exportCSV();};

let severity_style= ref('');
//define computed props
const page = usePage()
const currencies= (page.props.currencies)? page.props.currencies:[];

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.CONTAINS },
   
});

</script>

<template>

  
  <div class="card"  >
      <DataTable :value="ProductCount" v-model:filters="filters"  filterDisplay="row" ref="dt"
       :globalFilterFields="['name',]">
        <template #header>
          <div class="flex justify-between mobile:flex-col gap-2">
            <span class="p-input-icon-left">
                <i class="pi pi-search" />
                <InputText v-model="filters['global'].value" placeholder="Keyword Search" />
            </span>
            <button @click="exportCSV($event)" class=" flex-auto w-max p-2 bg-sky-900 text-white rounded-lg "  >
                 <span>Export</span> 
            </button>
          </div>
           

        </template>


          <Column field="product_id" header="product_id" class="mobile:hidden">
          </Column>

          <Column field="name"    header="Product name">
            <template #body="{ data }">
                  {{ data.name }}
            </template>
            
          </Column>

          <Column field="in_stock" header="In Stock"></Column>
         
      </DataTable>
  </div>


</template>