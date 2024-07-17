<script setup>

import { reactive ,computed,watch,onMounted,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import AppLayout from '@/Layouts/AppLayout.vue';
import searchStore from '../../searchStore.vue';
import Create from '@/Pages/Account/Create.vue'
import Update from '@/Pages/Account/Update.vue'
import Delete from '@/Pages/Account/Delete.vue'

import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';

import ColumnGroup from 'primevue/columngroup';   
import Row from 'primevue/row';                   
import { FilterMatchMode } from 'primevue/api';
import Button from 'primevue/button';
import TreeTable from 'primevue/treetable';
// import Tree from 'primevue/tree';
import Tree from '@/pages/Account/Tree.vue';

const app_layout = ref()
const MyTree = ref([])

onMounted(() => {
  // console.log(MyTree.value[0].ShowChildren)
  // console.log(MyTree.value[0].SubTree[0].ShowChildren)

})

function Open_Account_LedgerBook(account){
  app_layout.value.Open_Account_LedgerBook(account)
}

let props =defineProps({
  accounts:{   } ,
  statments:{   },
  test_account:{  },
})
//define computed props
const page = usePage()
const currencies= (page.props.currencies)? page.props.currencies:[];

let SearchBox = ref('') 
let SearchResult = ref() 

function focus_Account(accounts,event){
  accounts.forEach((account,index) => {
      if (account.name==event.value.name ) {
        searchStore.selected_account.value = account
        setTimeout(() => {
          document.getElementById(event.value.name).focus()
          document.getElementById(event.value.name).scrollIntoView()
        }, 100); 
      }
      if (account.children) {
        focus_Account(account.children,event)
      }
  });
}

let ancestors = ref([])
function get_ancestors(accounts,event){
  console.log(event.value)
  accounts.forEach((account,index) => {
    if (account.name==event.value.name ) {
        
         
      }
  });
}

// Search(props.accounts,'Assets')

function Scroll(text,event){
  console.log(text)
  setTimeout(() => {
    document.getElementById(event.value.name).focus()
    document.getElementById(event.value.name).scrollIntoView()
  }, 100);
}


// format number and make more readable for human by addind comma  
function Format(value){
    let formatter =Intl.NumberFormat('en')
    if (  !isNaN(Number(value))  &&  Number(value) != 0    ) {
        return formatter.format(value)
    }else{  
        return 0 
    }
}


const CreateModal = ref(null)
const DeleteModal = ref(null)
const UpdateModal = ref(null)

let ChosenAccount = ref(null)
let Parent_Of_Chosen_Account = ref(null)

function OpenCreateModal(){
  CreateModal.value.ShowCreateModal=true
}

function OpenUpdateModal(account,ParentAccount){
  ChosenAccount.value = account
  Parent_Of_Chosen_Account.value = ParentAccount
  UpdateModal.value.ShowUpdateModal = true
}

function OpenDeleteModal(account,ParentAccount){
  ChosenAccount.value = account
  Parent_Of_Chosen_Account.value = ParentAccount
  DeleteModal.value.ShowDeleteModal = true
}

</script>

<template>

  <AppLayout   ref="app_layout">

    <Create  ref="CreateModal" >
    </Create>

    <Update ref="UpdateModal" :account="ChosenAccount" :ParentAccount="Parent_Of_Chosen_Account" >
    </Update>

    <Delete  ref="DeleteModal" :account="ChosenAccount" :ParentAccount="Parent_Of_Chosen_Account"   >
    </Delete>

    <div class="w-full  " >
      <div class="flex justify-between items-center bg-sky-800">
        <h1 class="m-5 text-2xl text-white "> Accounts Chart </h1>
        <button @click="OpenCreateModal" class="mx-7 p-2 font-semibold rounded-lg text-sky-900 bg-white" >
           Create Account  
        </button>
      </div>

      <AutoComplete v-model="SearchBox" :suggestions="searchStore.available_accounts.value"
      @complete="searchStore.search_account" @item-select="focus_Account(accounts,$event)" 
      optionLabel="name" forceSelection  class="bg-white  w-6/12 my-3 ml-6 rounded-lg"
        :pt="{
            input: {
            class: ' h-full w-full py-2   dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
            },
        }">
      </AutoComplete>

      <div class="bg-white w-11/12 mx-auto h-full rounded-lg  ">
        <div  >
          <Tree  v-for="account in accounts" ref="MyTree"
            :account="account"  :IsActive="account.name==searchStore.selected_account.value?.name"
            @Open_Account_LedgerBook="Open_Account_LedgerBook"
            @OpenUpdateModal="OpenUpdateModal" @OpenDeleteModal="OpenDeleteModal"  >
          </Tree>
        </div>  
      </div>

    </div>
    

  </AppLayout>
  
    

</template>