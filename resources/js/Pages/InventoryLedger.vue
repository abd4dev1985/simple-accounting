<script setup>

import { reactive ,computed,watch,onMounted,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../searchStore.vue';
import DateObject from '../DateObject.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';   
import Row from 'primevue/row';                   



let props =defineProps({
  invoices:{   } ,

})




let severity_style= ref('');
//define computed props
const page = usePage()


const currencies= (page.props.currencies)? page.props.currencies:[];


</script>

<template>


  <div class="card"  >
      <DataTable :value="invoices" tableStyle="min-width: 50rem">
            <Column field="product_id" header="product_id"></Column>
            <Column field="invoiceable_type" header="invoiceable_type"></Column>
            <Column field="name" header="Product name"></Column>
            <Column field="quantity" header="Quantity"></Column>
            <Column field="price" header="Price"></Column>
            <Column field="ammount" header="Ammount"></Column>
      </DataTable>
  </div>


</template>