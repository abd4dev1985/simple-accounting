<script setup>

import { reactive ,computed,onUpdated,onMounted,ref,defineAsyncComponent  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import searchStore from '../../searchStore.vue';
import "primevue/resources/themes/lara-light-indigo/theme.css";

let props =defineProps({
    account:{   } ,
    ParentAccount: {    },
    IsActive: {     } ,
})

let ShowChildren =ref(false)

const emit = defineEmits([
    'Open_Account_LedgerBook','focus','OpenUpdateModal','OpenDeleteModal','ChildIsSelected',
])

onUpdated(() => {
    if (props.IsActive) {
        ShowChildren.value=true
        // console.log(props.IsActive)
        emit('ChildIsSelected')
    }
})

// const RecursiveChildLazy = defineAsyncComponent(() => import('./RecursiveChild.vue'))

function Open_Account_LedgerBook(account){
    emit('Open_Account_LedgerBook',account)
}

function OpenUpdateModal(account,ParentAccount=null){
    emit('OpenUpdateModal',account,ParentAccount)
}

function OpenDeleteModal(account,ParentAccount=null){
    emit('OpenDeleteModal',account,ParentAccount)
}

function ChildIsSelected (){
    ShowChildren.value=true
    emit('ChildIsSelected')
}



// create computed copy of account object without children key 
let computed_account= computed(()=>{
    return {id:props.account.id,name:props.account.name,number:props.account.number }
})

let SubTree = ref(null)
defineExpose({  ShowChildren ,SubTree  })


let ToogelShowChildren= ()=>{
    if (props.account.children) {
        ShowChildren.value = !ShowChildren.value
    }
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


// let MarginLeft = 'ml-'+props.account.level*3

let PaddingLift = function(level){
    return level*1.5 +'rem'
}




</script>

<template>
    
    <div :id="account.name" tabindex="1" @focus="console.log('jjjj')" class="flex justify-between w-full my-4 px-1  text-gray-700 border-b py-2 focus:bg-sky-100"  >
        <span @click="ShowChildren=!ShowChildren" class=" "
        :style="{'padding-left':  PaddingLift(account.level)}" >
            <svg  v-show="account.children"   data-slot="icon" class="inline h-3 w-4 rotate-90 mr-1 fill-slate-500" aria-hidden="true" fill="" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span class=" ml-0.5" >{{ account.number }} _  </span>
            <span >{{ account.name }}  </span>
        </span>

        
        <div class="flex gap-3 mr-11"  >

            <svg  @click="$emit('OpenDeleteModal',account,ParentAccount)" data-slot="icon" class="h-5 w-5"  aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>

            <div class="group relative">
                <svg   @click="$emit('OpenUpdateModal',account,ParentAccount)"  class="h-5 w-5"  data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path   d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span  class="hidden group-hover:block bg-sky-800 text-white p-1 rounded-lg text-sm absolute -top-8 -left-20 w-max z-10 " >
                    Edit Account
                </span>
            </div>
           
            <svg  @click="$emit('Open_Account_LedgerBook',account)" class="h-5 w-5" data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </div>
        
    </div>

    <div  v-show="ShowChildren"  class=" text-base" >
        <Tree v-for="(child,index) in account.children" :key="index" ref="SubTree"
        :account="child"  :ParentAccount="computed_account"
        :IsActive="child.name==searchStore.selected_account.value?.name"
        @Open_Account_LedgerBook="Open_Account_LedgerBook"
        @OpenUpdateModal='OpenUpdateModal' @ChildIsSelected="ChildIsSelected"
        @OpenDeleteModal="OpenDeleteModal"
        >
        </Tree>
    </div>

    



   

    
    

    

    <slot></slot> 

   
  
    
    
   

  

</template>