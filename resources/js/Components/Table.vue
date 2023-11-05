<script setup>
//import { router } from '@inertiajs/vue3' ;
import { reactive ,computed,watch,onUpdated,ref } from 'vue'
import { Head, Link, router,usePage, } from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../searchStore.vue';
import DialogModal from '@/Components/DialogModal.vue';
import ccc from '@/Components/ccc.vue';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";

const toast = useToast();

//define computed props
const page = usePage()
const errors = computed(() => page.props.errors)
const currencies= (page.props.currencies)? page.props.currencies:[];
const Etry_Lines = ( page.props.entry_lines)? page.props.entry_lines : [] ;

let document_number =ref(page.props.document.number);

searchStore.available_currencies.value = currencies


let rows_count = 30 + Etry_Lines.length;

let errorMassageModal = ref(false)

let default_account=ref('')
let collumns_count = 8



// create array from the lines of receipt (form)
let lines=[]
for (let index = 0; index < rows_count; index++) {
  if (Etry_Lines[index]) {

      lines[index] = {
      debit_amount:Etry_Lines[index].debit_amount,
      credit_amount:Etry_Lines[index].credit_amount,
      account:Etry_Lines[index].account,
      discreption:Etry_Lines[index].discreption,
      currencey:currencies.filter((currencey)=> currencey.id ==Etry_Lines[index].currency_id)[0]  ,
      currencey_rate:null,
      cost_center:Etry_Lines[index].cost_center,
      } 
  } else {

    lines[index] = {
    debit_amount:null,
    credit_amount:null,
    account:null,
    discreption:null,
    currencey:null,
    currencey_rate:null,
    cost_center:null ,
    }
    
  }

  
}
const form = ref(lines)
console.log(form)

let Equvalant_In_default_currney= computed(()=>{

  
}) 

const Entry_Totals =computed(()=>{
  let total_of_credit=0
  let total_of_debit=0
  form.value.forEach(line => {
    total_of_debit = !isNaN(Number(line.debit_amount)) && line.debit_amount !=null ? Number(line.debit_amount) + total_of_debit : total_of_debit
    total_of_credit = !isNaN(Number(line.credit_amount)) && line.credit_amount !=null  ? Number(line.credit_amount) + total_of_credit  : total_of_credit
  });
  return { debit_side:total_of_debit , credit_side: total_of_credit  }
});




//console.log(Number(number.replace(",","")))
const formatter =Intl.NumberFormat('en')

function format_number( value ){
  if (  typeof Number(value) == 'number' &&  Number(value) != 0    ) {
    return formatter.format(value)
  }else{  
    return value
  }

}

const rows = ref([])
const scrollable_table = ref()
const tableHeader=ref()


let keyboared_Navigation = ref(true)


let TableObject =computed(()=>{
  return {Table:scrollable_table.value ,TableHeader:tableHeader.value,Rows:rows.value, CollumnsCount:collumns_count}
})

/*
watch(scrollable_table, async (newTable, oldTble) => {
 
TableObject.value={Table:scrollable_table.value ,TableHeader:tableHeader.value,Rows:rows.value, CollumnsCount:collumns_count}
console.log('from table')
console.log(TableObject.value)
})
*/


let document_date = ref(new Date())


//inforce value of an element inside array to be a number  
function Force_Number_VALUE(array_objects,object_key ,index){
  if (isNaN(Number(array_objects[index][object_key])) && Number (array_objects[index][object_key]) !=null ) {
    array_objects[index][object_key] =null
  }
}
//inforce value of an element inside array to be a oject  
function Force_Object_VALUE(array_objects,object_key ,index){
  if (isNaN(Number(array_objects[index][object_key])) && Number (array_objects[index][object_key]) !=null ) {
    array_objects[index][object_key] =null
  }
}

//clear credit_amount input value if valide debit_amount input is inserted in the same line of form
function remove_credit_amount(index) {
  Force_Number_VALUE(form.value,'debit_amount',index)
  if (form.value[index].debit_amount  ) {
    form.value[index].credit_amount = null 
  }
 
}

// clear debit_amount input value if valide credit_amount input is inserted in the same line of form
function remove_debit_amount(index) {
  Force_Number_VALUE  (form.value,'credit_amount',index)
  if (form.value[index].credit_amount) {
    form.value[index].debit_amount =null
  }
}

/*  function check_balance()
    check the balance of entry,
    make sure the total of debit amount equal the total of credit amount
    return Boolean 
*/
function check_balance(){
    let total_debit = 0
    let total_credit = 0
    let accountFiled_is_missing = false 
    let amount_is_missing = false 
    let line_of_missing_AccountFiled = null
    let line_of_missing_Amount = null
    
    form.value.forEach((line,index)=>{
        total_debit += line.debit_amount
        total_credit += line.credit_amount
       
        // determin if account filed is missing in line 
        if ( !line.account_id && (line.credit_amount || line.debit_amount ) ) {
          accountFiled_is_missing=true
          line_of_missing_AccountFiled=index+1
        }
        // determin if ammount  is missing when account is exist  
        if ( line.account_id && ( !line.credit_amount && !line.debit_amount) ) {
          amount_is_missing=true
          line_of_missing_Amount=index+1
        }
    })


    if (accountFiled_is_missing) {
      alert("Account filed is missing in line"+line_of_missing_AccountFiled)
      return false
    }

    if (amount_is_missing) {
      alert("amountis missing in line"+line_of_missing_Amount)
      return false
    }

    if (total_debit !=total_credit) {
      //console.log(total_credit)
      //console.log(total_debit)
      alert("the entry not balanced ,total debit amount is not equal total credit amount")
      return false;
    }  
    return true 
}

function submit() {
     
  let document_catagory = page.props.document_catagory 

  let URL= '/'+ document_catagory.type +'/document_catagories/'+document_catagory.id
  let data={document_number:document_number.value ,document_catagory_id:document_catagory.id , lines:form.value}
  console.log(URL)
  router.post(URL, data,{
    onError:(errors)=>{
     // toast.add({ severity: 'danger', summary: 'Input Error', detail: 'Message Content', life: 5000 });
     
     //errorMassageModal.value=true
      for (const key in errors) {
        if (Object.hasOwnProperty.call(errors, key)) {
         let error = errors[key];
          toast.add({ severity: 'danger', summary: 'Input Error', detail:error , life: 10000 });
          console.log(error)
        }

      }
      
      },
  
    })

 
  
}


</script>
<template>

<div class="">
      
  <form class="sm:-mx-1 lg:-mx-2" @submit.prevent="submit">

    <div class="inline-block min-w-full  sm:px-6 lg:px-8">
      <div class="relative  w-11/12 overflow-auto  scrollbar-thin scrollbar-h-3 ">
        
        <div class=" flex justify-around mb-2  ">
          <!-- document_no input   -->
          <div>
            <label class="block text-transparent text-sm text-center" for="document_no">Invoice</label>
            <div class="flex justify-center items-center   text-sky-600">
              <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-9 h-9 rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>
              <div class="text-center relative">
                <input v-model="document_number" id="document_no"  class="block text-center mx-auto rounded-md w-14 h-8 text-gray-700 " >
                <!-- 
                <div v-if="errors.receipt_id" class="   text-red-500">{{ errors.receipt_id}}</div>
                -->
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-9 h-9 "><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>
            </div>
          </div>
          <!-- Default Account Input   -->
          <div>
            <label class="block text-sm  text-gray-900 font-semibold text-left" for="">Default Account</label>
            <AutoComplete v-model="default_account" :suggestions="searchStore.available_accounts.value"
              @complete="searchStore.search_account" optionLabel="name" forceSelection 
              :pt="{
                  input: { class: 'bg-white h-full py-2   dark:bg-gray-700 dark:text-gray-200  focus:ring-2' },
                }">
                <template #empty>
                    <div   class="font-semibold p-3 border-2 border-blue-500">
                        <div class=""> account <span class="text-blue-600">{{default_account }}</span> dose not exist </div>
                        <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                    </div>
                </template> 
            </AutoComplete>
          </div>
          <!-- DATE INPUT   -->
          <div>
            <label class="block text-sm text-center" for="">Date </label>
            <Calendar v-model="document_date" showIcon
              :pt="{
                  root:{class:' dark:bg-gray-700'},
                  input: { class: 'bg-white text-center h-13 dark:bg-gray-700 dark:text-gray-200  focus:ring-2' },
              }"
            />
          </div>
        
        </div>

        <div class=" flex justify-around mb-2  ">

          <!-- Currncey Rate input   -->
          
          <div>
            <label class="block text-sm text-center" for="">Rate </label>
            <input class="h-8 w-24 ">
          </div>
          

          <!-- Currncey Input   -->
          <div>
            <label class="block text-sm text-center w-28   " for=""> Currncey Rate</label>
            <AutoComplete v-model="default_account" :suggestions="searchStore.available_accounts.value"
              @complete="searchStore.search_account" optionLabel="name" forceSelection 
              :pt="{
                  input: { class: 'bg-white h-full w-28 py-1 text-center dark:bg-gray-700 dark:text-gray-200  focus:ring-2' },
                }">
                <template #empty>
                    <div   class="font-semibold p-3 border-2 border-blue-500">
                        <div class=""> account <span class="text-blue-600">{{default_account }}</span> dose not exist </div>
                        <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                    </div>
                </template> 
            </AutoComplete>
          </div>
          <!-- Discreption INPUT   -->
          <div>
            <label class="block text-sm text-center" for="">Discreption </label>
          <input class="h-8 w-72 rounded-md border-2" >
          </div>

        </div>
          

        <!-- entry table   -->
        <div ref="scrollable_table"  class="h-96  relative  overflow-auto scrollbar " >
          <table class="min-w-full   text-center border-collapse text-sm font-light">
              
                  <thead ref="tableHeader" class="sticky top-0 z-10 bg-white border-b-2 font-medium dark:border-neutral-500">
                      <tr >
                      <th scope="col" class=" py-4">#</th>
                      <th scope="col" class="py-4"> Debite</th>
                      <th scope="col" class=" ">Credite</th>
                      <th scope="col" class=" py-4">Account</th>
                      <th scope="col" class=" block overflow-auto resize-x py-4 min-w-[200px] ">Discreption</th>
                      <th scope="col" class=" py-4">Currencey</th>
                      <th scope="col" class=" py-4">rate</th>
                      <th scope="col" class=" py-4">cost center</th>
                      <th scope="col" class="py-4">Heading</th>
                      </tr>
                  </thead>
                  
                  <tbody> 
                      <tr v-for="(i,index) in rows_count " :key="index" ref="rows" class="odd:bg-white even:bg-slate-200 dark:border-neutral-500">
                        <td class="  px-4 text-center py-4 font-medium">
                            <div class="m-auto w-2">{{index+1}}</div>                    
                        </td>

                        <td class="whitespace-nowrap border border-gray-400 ">
                          <ccc  v-model="form[index].debit_amount"   @change="remove_credit_amount(index)"  
                          :TableObject="TableObject"  :rows_index="index" :columns_index=1  Format="number" />
                        </td>

                        <td class="whitespace-nowrap  border border-gray-400 ">
                          <ccc  v-model="form[index].credit_amount"   @change="remove_debit_amount(index)"  
                          :TableObject="TableObject"  :rows_index="index" :columns_index=2  Format="number" />
                        </td>

                        <td class="whitespace-nowrap border border-gray-400 relative  ">                         
                          <ccc v-model="form[index].account" :TableObject="TableObject"  :rows_index="index" :columns_index=3
                          Format="aoutcomplete" :SearchFunction="searchStore.search_account" :Suggestions="searchStore.available_accounts.value" >  
                            <template #emptySuggestions>
                              <div class=""> account <span class="text-blue-600">{{form[index].account }}</span> dose not exist </div>
                              <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                            </template>
                          </ccc>
                        </td>

                        <td class="whitespace-nowrap border border-gray-400 ">
                          <ccc  v-model="form[index].discreption"   
                          :TableObject="TableObject"  :rows_index="index" :columns_index=4  Format="text" />
                        </td>

                        <td class="whitespace-nowrap border border-gray-400">
                          <ccc v-model="form[index].currencey" :TableObject="TableObject"  :rows_index="index" :columns_index=5
                          Format="aoutcomplete" :SearchFunction="searchStore.search_currencey" :Suggestions="searchStore.filterd_currencies.value" >  
                            <template #emptySuggestions>
                              <div class=""> currencey <span class="text-blue-600">{{form[index].currencey }}</span> dose not exist </div>
                            </template>
                          </ccc>
                        </td>

                        <td class="whitespace-nowrap border border-gray-400">
                          
                          <ccc v-model="form[index].currencey_rate"  :TableObject="TableObject"  :rows_index="index" :columns_index=6 
                              Format="number" 
                          />
                          
                        </td>

                        <td class="whitespace-nowrap border border-gray-400">

                          <ccc v-model="form[index].cost_center" :TableObject="TableObject"  :rows_index="index" :columns_index=7
                          Format="aoutcomplete" :SearchFunction="searchStore.search_cost_center"
                          :Suggestions="searchStore.available_cost_centers.value" >
                            <template #emptySuggestions>
                              <div class=""> cost center <span class="text-blue-600">{{form[index].cost_center }}</span> dose not exist </div>
                              <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                            </template>
                          </ccc>
                          
                        </td>

                        <td class="whitespace-nowrap border border-gray-400">

                          
                        </td>
                      </tr>
                  </tbody>
                  
          </table>
        </div>

        <div class="mx-10 w-60 flex justify-around bg-sky-200  ">
          <div class="">{{ Entry_Totals.debit_side }}</div>
          <div class="" >{{ Entry_Totals.credit_side }}</div>

        </div>
        <div class="">

        </div>
      </div>
    </div>
    <div>
      <button class="ml-11"  type="submit" >create</button>
    </div>
    
    
    
  </form>

  <DialogModal :show="errorMassageModal" @close="errorMassageModal=false">
                <template #title >
                  
                    <h1 class="text-red-500">
                      Invalid Enputs
                    </h1>  
                
                </template>

                <template #content>
                  Invalid data was submited ,please check the following listed erorrs  and try to fix them as we suggest. Once you fix them , the form can be submitted 

                    <div class="mt-4 bg-red-600 text-white ">
                      <div   v-for="error in errors ">
                        {{ error }}
                      </div>
                     
                    </div>
                </template>

                <template #footer>
                  <button   @click="errorMassageModal=false" >
                    ok
                  </button>
                   
                </template>
  </DialogModal>

  <Toast 
  :pt="{ 
        root:{class: 'opacity-95'},
        content: { class: ' bg-red-600 text-white' },
    }"
  
  />



</div>
</template>