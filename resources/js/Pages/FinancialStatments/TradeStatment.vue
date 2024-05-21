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

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.CONTAINS },
   
});

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


function get_account_balance (account){
    if (account.children) {
        let  total_balances = 0
        account.children.forEach(child => {
           // console.log(child.name)
            total_balances =  total_balances + get_account_balance(child)
        })
        return total_balances

    }else{
        return (account.balance)? Number(account.balance):Number(0)
    }

}

function get_accounts_nodes(accounts){
    console.log('accounts')
    console.log(accounts)

    let node=  accounts.map(function(account){
        let obj={
            key: account.id,
            data:{ 
                    id:account.id ,
                    name:account.name ,
                    balance:format_number( get_account_balance(account) ),  
                },
            children:(account.children)? get_accounts_nodes(account.children) :null 
        }
        return obj
    });
    
    return node
}


let accounts_nodes =ref()
onMounted(() => {
    accounts_nodes.value=get_accounts_nodes(props.accounts.Net_Purchases)
});




</script>

<template>
  
    <div class="hidden"  v-for="(account, index) in accounts"  >  
        <div  class="flex justify-start"  >
            <div class="m-3">{{account.id}}</div>
            <div  class="m-3">{{account.name}}</div>
        </div>
        <div v-if="account.children" v-for="(child, index) in account.children"  class="flex justify-start"  >
            <div class="m-3">{{child.id}}</div>
            <div  class="m-3">{{child.name}}</div>
        </div>
    </div>
    <Button class="hidden" @click="toggleApplications" label="Toggle Applications" />
    <TreeTable v-model:expandedKeys="expandedKeys" showGridlines:true   :value="accounts_nodes"
      :pt="{
          header:{class:  'bg-black'}
    }">
        <Column field="name" header="Name" expander></Column>
        <Column field="balance" header="Balance"></Column>
    </TreeTable>

  


</template>