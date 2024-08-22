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
const emit = defineEmits(['New_Line_Added']) 
let Chosen_Custom_fields = ref( [])
let ShoWDetail = ref(false)

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
    return   isNaN(Number(value)) ? false:true  
}
function open_Entry_Modal(){
   // console.log('from slot')
     ShowEntryModal.value = true 
}
function Close_Without_Save(){
   // console.log('Close_Without_Save')
   // console.log(props.default_line)
    line.value ={...props.default_line} 
    ShowEntryModal.value = false 
}

function Close_Entry_Modal(){
    if (line.value.account && Number(line.value.debit_amount)+ Number(line.value.credit_amount)>0) {
        emit('New_Line_Added');
        ShowEntryModal.value = false 
    }
}
function force_number(){
 if ( isNaN( Number(line.value.debit_amount) )    ) {
    line.value.debit_amount=null ;
  }
  if ( isNaN( Number(line.value.credit_amount) )    ) {
    line.value.credit_amount=null ;
  }
}
//clear credit_amount input value if valide debit_amount input is inserted in the same line of entry
function remove_credit_amount() {
   force_number()
   if (line.value.debit_amount   ) {
        line.value.credit_amount = null 
    }
}
// clear debit_amount input value if valide credit_amount input is inserted in the same line of entry
function remove_debit_amount() {
    force_number()
    if (line.value.credit_amount ) {
        line.value.debit_amount =null
    }
}


</script>
<template>
    <slot    :ShowEntryModal="ShowEntryModal" :open_Entry_Modal="open_Entry_Modal" ></slot>
    <!--    line for mobile   -->
    <div v-if="line.account"  class="bg-white p-2 text-gray-800 my-3">
        <div  class="font-semibold flex justify-start gap-2" :class="{'text-red-700':line.debit_amount,'text-blue-800':line.credit_amount }" >
            <div  >
                {{line.account?.name}}
            </div>
            <div  class="flex justify-end flex-auto  w-1/2 " >
                <div v-if="line.debit_amount" class=" mr-1 " >
                     Debit;  {{ format_number(line.debit_amount)}} 
                </div>
                <span v-if="line.credit_amount" class="mr-1 " > 
                    Credit;  {{ format_number(line.credit_amount)}}
                </span>
                <svg v-show="!ShoWDetail" @click="ShoWDetail=true"  class="ml-1 h-6 w-5"  data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="m19.5 8.25-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <svg v-show="ShoWDetail" @click="ShoWDetail=false"   class="ml-1  h-6 w-5"  aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="m4.5 15.75 7.5-7.5 7.5 7.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div> 
        </div>

         <!-- entry detail  -->
        <div v-show="ShoWDetail"  :class="{'text-red-700':line.debit_amount,'text-blue-800':line.credit_amount }"  class="my-1 space-y-1">
            
            <div> <span class="font-semibold ">Description :  </span>
               <span class="text-sm ml-0.5">  {{ line.description }} </span>
            </div>
            <div> <span class="font-semibold ">Currency :  </span>
               <span class="text-sm ml-0.5">  {{ line.currency.name }} Rate {{  line.currency_rate  }} </span>
            </div>
            <div v-for="(field, name) in line.customfields" class="flex gap-6  my-5">
                <div v-if="field">
                    <span class="font-semibold "> {{ name }} : </span>
                    <span  class="text-sm ml-0.5"> {{ field}} </span>
                </div>
            </div>
            <div class="text-right font-semibold ">
                <span @click="ShowEntryModal=true"  >Edite</span>  
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
            <div class="flex flex-col  my-5">
                <label for="">Debit Ammount</label>
                <div class="relative group bg-white dark:bg-black dark:text-white ">
                    <input v-model="line.debit_amount"  @change="remove_credit_amount"
                    class="bg-inherit text-transparent focus:text-gray-950 dark:focus:text-gray-200 p-3 w-full rounded-md ring-offset-1 border border-gray-300 focus:ring-1 "> 
                    <div  @click="focus_input"   class="block absolute  group-focus-within:hidden px-3   h-1/3  w-full top-1/2 left-1/2  -translate-x-1/2 -translate-y-1/2   ">
                        {{ format_number(line.debit_amount) }}
                    </div>  
                </div>    
            </div>
            <div class="flex flex-col  my-5">
                <label for="">Credit Ammount</label>
                <div class="relative group bg-white dark:bg-black dark:text-white ">
                    <input v-model="line.credit_amount"  @change="remove_debit_amount"
                    class="bg-inherit text-transparent focus:text-gray-950 dark:focus:text-gray-200 p-3 w-full rounded-md ring-offset-1 border border-gray-300 focus:ring-1 "> 
                    <div  @click="focus_input"  class="block absolute  group-focus-within:hidden px-3   h-1/3  w-full top-1/2 left-1/2  -translate-x-1/2 -translate-y-1/2   ">
                        {{ format_number(line.credit_amount) }}
                    </div>  
                </div>    
            </div>
            <div class="  my-5">
                <label for="">Description</label>
                <div class="relative group bg-white  dark:bg-black dark:text-white ">
                    <textarea  v-model="line.description" rows="6" class="p-3 w-full overflow-auto text-gray-800 rounded-md ring-offset-1 border border-gray-300 focus:ring-1" >
                    </textarea>
                </div>    
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

              <PrimaryButton class="p-2 mx-4 font-semibold rounded-md" @click="Close_Entry_Modal"   >
                  Add Entry Line
              </PrimaryButton>
          </template>
    </DialogModal>




</template>

