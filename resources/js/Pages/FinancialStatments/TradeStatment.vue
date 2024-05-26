<script setup>

import { reactive ,computed,watch,onMounted,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import AppLayout from '@/Layouts/AppLayout.vue';

import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';

import ColumnGroup from 'primevue/columngroup';   
import Row from 'primevue/row';                   
import { FilterMatchMode } from 'primevue/api';
import Button from 'primevue/button';
import TreeTable from 'primevue/treetable';
import Tree from 'primevue/tree';
import AccountTree from '@/pages/Account/AccountTree.vue';

import Column from 'primevue/column';


let props =defineProps({
  accounts:{   } ,

})
const expandedKeys = {1:true};
const toggleApplications = () => {
    let _expandedKeys = { ...expandedKeys.value };
    if (_expandedKeys['0']) delete _expandedKeys['0'];
    else _expandedKeys['0'] = true;

    expandedKeys.value = _expandedKeys;
}






const dt = ref();
const exportCSV = () => { dt.value.exportCSV()}


let severity_style= ref('');
//define computed props
const page = usePage()
const currencies= (page.props.currencies)? page.props.currencies:[];


const formatter =Intl.NumberFormat('en')
function format_number( value ){
  if (  !isNaN(Number(value))  &&  Number(value) != 0    ) {
    return formatter.format(value)
  }else{  
    return null 
  }

}
function unformat_number( value ){
  if ( isNaN(value )) {
    return formatter.format(value)
  }else{  
    return null 
  }

}


</script>

<template>

    <div class="space-y-8" >
      <div>
          <AccountTree :account="accounts.Beginning_Inventory"  >
          </AccountTree>
      </div>

      <div>
          <AccountTree :account="accounts.Net_Purchases"  >
          </AccountTree>
      </div>
      <div>
          <AccountTree :account="accounts.Net_Sales"  >
          </AccountTree>
      </div>
      <div>
        <h1>Ending Iventory Cost</h1>
        <div> {{ accounts.Ending_Iventory_cost }} </div>
      </div>
        
    </div>


</template>