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
import InputText from 'primevue/inputtext';

import ColumnGroup from 'primevue/columngroup';   
import Row from 'primevue/row';                   
import { FilterMatchMode } from 'primevue/api';
import Button from 'primevue/button';
import TreeTable from 'primevue/treetable';
import Tree from 'primevue/tree';

import Column from 'primevue/column';
import { data } from 'autoprefixer';


let props =defineProps({
  accounts:{   } ,

})
let expandedKeys = reactive({});

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

function get_accounts_nodes(accounts,parent_name=null ){
    let node=  accounts.map(function(account){
        expandedKeys[account.id]=true
        let obj={
            key: account.id,
            data:{ 
                    id:account.id ,
                    name:account.name ,
                    debit:(account.balance>0)? format_number( account.balance ):0,
                    credit:(account.balance<0)? format_number( -1*account.balance ):0,
                    has_children:(account.children) ?true:false ,
                    parent_name:parent_name,
                    balance:format_number( account.balance ),  
                },
            children:(account.children)? get_accounts_nodes(account.children,account.name) :null 
        }
        return obj
    });
    
    return node
}


let accounts_nodes =ref()
onMounted(() => {
    accounts_nodes.value=get_accounts_nodes(props.accounts)
 

});

function show_data(data){
    console.log('data')

}
</script>

<template>
  
    <div class="hidden"  v-for="(account, index) in accounts"  >  
        <div  class="flex justify-start"  >
            <div class="m-3">{{account.id}}</div>
            <div  class="m-3">{{account.name}}</div>
        </div>
        <div v-if="account.children" v-for="(child, index) in account.children"  class="flex justify-start"  >
            <div class="m-3">{{child.id}}</div>
            <div  class="m-3 bg-red-700">{{child.name}}</div>
        </div>
    </div>
    <Button class="hidden " @click="toggleApplications" label="Toggle Applications" />

    <TreeTable v-model:expandedKeys="expandedKeys"showGridlines:true 
     :value="accounts_nodes" scrollable >

        <Column  field="name" header="Name" expander style="width:25%" >
          <template #body="propsbody">
            <span :class="{'text-red-700 font-semibold':(propsbody.node.data.has_children)}"
            class="inline-block  ">
                {{propsbody.node.data.name}}
            </span>
          </template>
        </Column>

        <Column field="debit" header="Debit" style="width:25%"  >
            <template #body="propsbody">
                <span :class="{'text-red-700 font-semibold':(propsbody.node.data.has_children)}"
                class="inline-block  ">
                    {{propsbody.node.data.debit}}
                </span>
            </template>
        </Column>

        <Column field="credit" header="Credit" style="width:25%"  >
            <template #body="propsbody">
                <span :class="{'text-red-700 font-semibold':(propsbody.node.data.has_children)}"
                class="inline-block  ">
                    {{propsbody.node.data.credit}}
                </span>
            </template>
        </Column>

        <Column field="parent_name" header="Parent" style="width:25%"  >
            <template #body="propsbody">
                <span :class="{'text-red-700 font-semibold':(propsbody.node.data.has_children)}"
                class="inline-block  ">
                    {{propsbody.node.data.parent_name}}
                </span>
            </template>
        </Column>

    </TreeTable>

  


</template>