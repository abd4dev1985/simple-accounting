<script setup>
import { reactive ,computed,watch,onUpdated,ref,  } from 'vue'
import AutoComplete from 'primevue/autocomplete';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../../searchStore.vue';
import DialogModal  from '@/Components/DialogModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import PrimaryButton from '@/Components/PrimaryButton.vue';    
import ccc from '@/Components/ccc.vue';

let props= defineProps({
    default_line:{  default:[]}
})
const line =  defineModel('line');
const emit = defineEmits(['change','New_Line_Added']) 
let Chosen_Custom_fields = ref( [])

// setup  Chosen Custom fields
if (props.default_line.customfields) {
    for ( var key in props.default_line.customfields ) {
        if (props.default_line.customfields.hasOwnProperty(key)) {
            if (props.default_line.customfields[key]) {
                Chosen_Custom_fields.value.push({name:key})
            }
        }
    }
    // let fields = Object.keys(props.default_line.customfields)
    // Chosen_Custom_fields.value = fields.map( (field)=>{
    //     return {name:field}
    // }) 
}





function format_number( value ){
  let formatter =Intl.NumberFormat('en') 
  if (  !isNaN(Number(value))  &&  Number(value) != 0    ) {
    return formatter.format(value)
  }else{  
    return null 
  }

}

function focus_input(event){
  event.target.previousElementSibling.focus()
}

let ShowEntryModal = ref(false)
let Changes_is_Accepted = ref(false)

function Is_Number(value){
    return  ( isNaN(Number(value)) || Number(value)==0  )? false:true  
}
function open_Entry_Modal(){
   // console.log('from slot')
     ShowEntryModal.value = true 
}
function Close_Without_Save(){
    line.value ={...props.default_line} 
    ShowEntryModal.value = false 
}

function Close_Product_Modal(){
    if (line.value.product && Is_Number(line.value.price) && Is_Number(line.value.quantity)) {
        emit('New_Line_Added');
        ShowEntryModal.value = false 
    }
}
function force_number(event){
 if ( isNaN( Number(event.target.value) )    ) {
  event.target.value=null ;
  }
}


</script>
<template>
    <slot    :ShowEntryModal="ShowEntryModal" :open_Entry_Modal="open_Entry_Modal" ></slot>
    <!-- invoice table for mobile   -->
    <div v-if="line.account"  class="bg-white p-2 text-gray-800 space-y-1">
        <div class="font-semibold flex justify-between">
            <span >Account name : {{line.account?.name}}</span>
            <span  @click="ShowEntryModal=true" class="tex" > Edite </span>  
        </div>
        <div class="text-sm" >
            {{line.debit}}  each = &nbsp; &nbsp;&nbsp; {{ format_number(line.debit) }}
        </div>
        <div v-for="(field, name) in line.customfields" class="flex gap-6  my-5">
            <div v-if="field">
                <span> {{ name }} : </span>
                <span> {{ field}} </span>
            </div>

        </div>
    </div>

    <DialogModal :show="ShowEntryModal" @close="Close_Without_Save">
          <template #title>
              Edit Line
          </template>

          <template #content>
            <div class="flex flex-col my-8">
                <label for="">account name</label>
                <AutoComplete v-model="line.account" :suggestions="searchStore.available_accounts.value"
                @complete="searchStore.search_account" optionLabel="name"
                :pt="{
                    input:{class: 'w-full'}

                }">
                </AutoComplete>
            </div>
            <div class="flex flex-col  my-8">
                <label for="">Price</label>
                <div class="relative group bg-white dark:bg-black dark:text-white ">
                    <input v-model="line.price"  @focus="force_number" @change="$emit('change')"
                    class="bg-inherit text-transparent focus:text-gray-950 dark:focus:text-gray-200 p-3 w-full rounded-md ring-offset-1 border border-gray-300 focus:ring-1 "> 
                    <div  @click="focus_input"  class="block absolute  group-focus-within:hidden px-3   h-1/3  w-full top-1/2 left-1/2  -translate-x-1/2 -translate-y-1/2   ">
                        {{ format_number(line.price) }}
                    </div>  
                </div>    
            </div>

            <div class="flex flex-col my-8">
                <label for="">Quantity</label>
                <input  v-model="line.quantity"  @change="$emit('change')"class=" focus:text-gray-950 dark:focus:text-gray-200 p-3
                 w-full rounded-md ring-offset-1 border border-gray-300 focus:ring-1 "  >
            </div>

            <div class="flex flex-col my-5">
                <label for="">Add Custom fields </label>
                <AutoComplete v-model="Chosen_Custom_fields" :suggestions="searchStore.available_custom_fields.value"
                @complete="searchStore.search_custom_fieled" optionLabel="name"  multiple 
                :pt="{
                    input:{class: 'w-full border'},
                    inputToken:{class:' border-2'},

                }">
                </AutoComplete>
            </div>

            <div v-for="(field, key) in Chosen_Custom_fields" class="flex flex-col my-5">
                <label>  {{ field?.name }} </label>
                <input  v-model="line.customfields[field?.name]"  class=" focus:text-gray-950 dark:focus:text-gray-200 p-3
                min-w-max rounded-md ring-offset-1 border border-gray-300 focus:ring-1  "  >
            </div>


            
          </template>

          <template #footer>
              <SecondaryButton class="p-2 mx-4 font-semibold rounded-md"   @click="Close_Without_Save">
                  Back
              </SecondaryButton>

              <PrimaryButton class="p-2 mx-4 font-semibold rounded-md" @click="Close_Product_Modal"   >
                  Add Product
              </PrimaryButton>
          </template>
    </DialogModal>




</template>

