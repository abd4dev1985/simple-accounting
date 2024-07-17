<script setup>

import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive ,computed,watch,onUpdated,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../searchStore.vue';
import DateObject from '../DateObject.vue';
import Language from '@/Pages/Language.vue';

import ConfirmationModal  from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import DangerButton from '@/Components/DangerButton.vue';    
import ccc from '@/Components/ccc.vue';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import { useWinBox } from 'vue-winbox'

const toast = useToast();
const createWindow = useWinBox()

let severity_style= ref('');
let Translate = Language.Translate

 console.log('Language')
 console.log(Language.CurrentLanguage.value)
 console.log(Translate('jhjhjkhkjhjk'))





let props =defineProps({
    entry_lines:{ type: Array , default:[] }   ,
    document_catagory:{   }   ,
    accounts:{},
    document : {        },
    new_document_number:{     },
    columns_count:{},
    customfields:{type:Array },
    currencies : { type: Array , default:[]   },
    operation :{ type:String , default: 'update'  },
    delete_url:{},
    update_url:{},
    pervious_document_url :{  },
    next_document_url :{  },
})

function get_standard_object(){
  let standard_object ={}
    props.customfields.forEach(field => {
      standard_object[field]=null
    });
    return standard_object 
}

//define computed props
const page = usePage()
// const errors = computed(() => page.props.errors)
const currencies= (page.props.currencies)? page.props.currencies:[];
console.log(currencies[0])
const Etry_Lines = ( page.props.entry_lines)? page.props.entry_lines : [] ;

let document_number =ref( (props.document)? props.document.number: props.new_document_number );

let document_date = ref( (props.document)? props.document.date : new Date()  )

searchStore.available_currencies.value = currencies

let form_have_been_adjusted = ref(false);

let rows_count = 30 + Etry_Lines.length;

let DeleteModal = ref(false)

let default_account=ref('')


// create array from the lines of receipt (form)
let lines=[]
for (let index = 0; index < rows_count; index++) {
  if (Etry_Lines[index]) {

      lines[index] = {
      id: Etry_Lines[index].id,
      debit_amount:Etry_Lines[index].debit_amount,
      credit_amount:Etry_Lines[index].credit_amount,
      account:Etry_Lines[index].account,
      description:Etry_Lines[index].description,
      currency:currencies.filter((currency)=> currency.id ==Etry_Lines[index].currency_id) [0]  ,
      currency_rate:1,
      cost_center:Etry_Lines[index].cost_center,
      customfields: (Etry_Lines[index].customfields)? JSON.parse(Etry_Lines[index].customfields ): get_standard_object(),
      } 
  } else {

    lines[index] = {
    debit_amount:null,
    credit_amount:null,
    account:null,
    description:null,
    currency:currencies[0],
    currency_rate:1,
    cost_center:null ,
    customfields: get_standard_object(),

    }
    
  }

}
let form = ref(lines)

function set_currencey_rate_line(rate,index ){
  console.log(rate)
  form.value[index].currency_rate = rate
}


// watch( [document_number,document_date,form], () => {
 // form_have_been_adjusted.value=true
 // console.log('form updated')
//},  { deep: true },{ immediate: true })

//watch(form.value, () => {
 // form_have_been_adjusted.value=true
//console.log('form updated')
//}  ,{ deep: true },{ immediate: true })

const Entry_Totals =computed(()=>{
  let total_of_credit=0
  let total_of_debit=0
  form.value.forEach(line => {
    total_of_debit = !isNaN(Number(line.debit_amount)) && line.debit_amount !=null ? Number(line.debit_amount) + total_of_debit : total_of_debit
    total_of_credit = !isNaN(Number(line.credit_amount)) && line.credit_amount !=null  ? Number(line.credit_amount) + total_of_credit  : total_of_credit
  });
  return { debit_side:total_of_debit , credit_side: total_of_credit  }
});


const rows = ref([])
const scrollable_table = ref()

let column_index=0


function   get_columen_index(){
    console.log("dd")
    column_index ++
    return column_index
}

const tableHeader=ref()


let TableObject =computed(()=>{
  return {
    Table:scrollable_table.value ,TableHeader:tableHeader.value,Rows:rows.value,
    CollumnsCount:props.columns_count + props.customfields.length}
})

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
 // form_have_been_adjusted.value=true
  Force_Number_VALUE(form.value,'debit_amount',index)
  if (form.value[index].debit_amount  ) {
    form.value[index].credit_amount = null 
  }
 
}

// clear debit_amount input value if valide credit_amount input is inserted in the same line of form
function remove_debit_amount(index) {
  //form_have_been_adjusted.value=true
  Force_Number_VALUE  (form.value,'credit_amount',index)
  if (form.value[index].credit_amount) {
    form.value[index].debit_amount =null
  }
}
function clear_form(){
  rows_count = 30 
  lines=[]
  for(let index = 0; index < rows_count; index++) {
    lines[index] ={
      debit_amount:null,credit_amount:null,account:null,description:null,currency:null,
      currency_rate:1,cost_center:null ,customfields: get_standard_object(),
    }
  }
  form.value= lines
  document_number.value=(props.document)? props.document.number: props.new_document_number

}

function submit() {
  if (props.operation=="update") {
    update_document()
  }else{
      create_document()
  }
  
}

function delete_document(){
  router.delete(props.delete_url,{
    onSuccess: page => {
      severity_style.value ='bg-green-400 text-white'
      DeleteModal.value=false
      toast.add({ severity: 'success', summary: 'successfully deleted', detail:'kkk' , life: 3000 });
      clear_form()
    },  

  }
  )
}

function update_document() {
  let data={
    document_number:document_number.value ,
    document_catagory_id:page.props.document_catagory.id  ,
    entry_lines:form.value ,
    date:DateObject.ToString(document_date.value)
  }

  router.put(props.update_url, data,{
    onError:(errors)=>{
      //errorMassageModal.value=true
      for (const key in errors) {
        if (Object.hasOwnProperty.call(errors, key)) {
          severity_style.value ='bg-red-600 text-white'
          let error = errors[key];
          toast.add({ severity: 'danger', summary: 'Input Error', detail:error , life: 10000 });
          console.log(error)
        }
      }
    },
    onSuccess: page => {
      severity_style.value ='bg-green-400 text-white'
      toast.add({ severity: 'success', summary: 'successfully updated', detail:'kkk' , life: 3000 });
    },  
  })
    
}

function create_document(){
  let document_catagory = page.props.document_catagory 
  
  let URL= '/'+ document_catagory.type +'/document_catagories/'+document_catagory.id

  let data={
    document_number:document_number.value ,
    document_catagory_id:document_catagory.id ,
    entry_lines:form.value ,
    date:DateObject.ToString(document_date.value),
  }
  router.post(URL, data,{
    onError:(errors)=>{
      //errorMassageModal.value=true
      for (const key in errors) {
        if (Object.hasOwnProperty.call(errors, key)) {
          severity_style.value ='bg-red-600 text-white'
          let error = errors[key];
          toast.add({ severity: 'danger', summary: 'Input Error', detail:error , life: 10000 });
          console.log(error)
        }
      }
    },
    onSuccess: page => {
      severity_style.value ='bg-green-400 text-white'
      toast.add({ severity: 'success', summary: 'New entry added', detail:'kkk' , life: 3000 });
      clear_form()
    },
  })
}


</script>

<template>
    <AppLayout title="Dashboard">
        <div class=" dark:bg-gray-800   shadow-xl sm:rounded-lg">
          
          <h1  class="flex  justify-start text-xl w-full bg-sky-700 text-white  px-4  py-2">
            <div class="flex-shrink font-semibold">
              <span v-if="operation=='create'" >New </span> {{ document_catagory.name }}
            </div> 
            <div class="flex-shrink  flex justify-end items-center  mx-4  text-sky-600 ">
              <Link v-if="pervious_document_url"  :href="pervious_document_url">
                <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-9 h-9 rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>
              </Link>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="#d1d5db" viewBox="0 0 24 24" stroke-width="1.5" stroke="#d1d5db" class="w-9 h-9 rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>
              <div class=" text-center relative">                
                <input v-model="document_number" id="document_no" form="myform" class=" text-center  rounded-md w-12 h-6 text-gray-700 " >
              </div>
              
              <Link v-if="next_document_url" :href="next_document_url"  >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-9 h-9 "><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>
              </Link>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="#d1d5db" viewBox="0 0 24 24" stroke-width="1.5" stroke="#d1d5db" class="w-9 h-9 "><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>

            </div>

          </h1>
          <div class="flex flex-col  tab:flex-row dark:text-gray-200 justify-between mx-3 my-4 ">
           
            <!-- INPUT DOCUMENT NUMBER -->
            <div class="flex-initial  w-max">
              <div class="text-center relative">
                <label class="block font-semibold text-left text-sm dark:text-gray-200 text-black " for="document_no">Entry #</label>
                <input v-model="document_number" id="document_no" form="myform" class="block border text-center mx-auto rounded-md w-14 h-8 text-gray-700 " >
                <!-- 
                <div v-if="errors.receipt_id" class="   text-red-500">{{ errors.receipt_id}}</div>
                -->
              </div>

            </div>
            
            <!-- Default Account Input   -->
            <div class="flex-initial ">
              <label class="block text-sm font-semibold text-left" for="">Default Account</label>
              <AutoComplete v-model="default_account" :suggestions="searchStore.available_accounts.value"
                @complete="searchStore.search_account" optionLabel="name" forceSelection 
                :pt="{
                    input: {
                      class: 'bg-white h-8 w-44 py-2   dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                      form : 'myform' ,
                    },
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
            <div class="flex-initial ">
              <label class="block text-sm font-semibold text-left" for=""> Date     </label>
              <Calendar v-model="document_date" showIcon  dateFormat="dd/mm/yy"
                :pt="{
                    root:{class:' dark:bg-gray-700'},
                    input: { 
                      class: 'bg-white text-center h-8 w-32 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                      form:'myform',
                    },
                    dropdownButton: {
                      root: { class: 'h-8' }
                    }
                }"
              />
            </div>

          </div>
              
           
            
            <form class="sm:-mx-1 lg:-mx-2 " id="myform" @submit.prevent="submit">
               <!-- entry table   -->
               <div ref="scrollable_table"  class=" lg:h-[410px] mx-6 relative  overflow-auto scrollbar larg:max-w-[73vw] " >
                    <table class=" dark:text-gray-200   text-center border-separate   text-sm font-light">
                        
                            <thead ref="tableHeader"  class="sticky top-0 z-[20] bg-white dark:bg-gray-700 bg-inherit  border-2 font-medium dark:border-neutral-400">
                                <tr class="" >
                                <th scope="col " class=" border-r b py-4 sticky  z-[10] left-0   bg-gray-200 ">#</th>
                                <th scope="col" class=" border-r border-b  py-4  "> Debite</th>
                                <th scope="col" class="border-r border-b  dark:border-neutral-400 py-4  ">Credite</th>
                                <th scope="col" class=" border-r border-b  dark:border-neutral-400  py-4">Account</th>
                                <th scope="col" class=" block border-r border-b  overflow-auto resize-x py-4 min-w-[200px] ">Description</th>
                                <th scope="col" class="border-r border-b  dark:border-neutral-400 py-4">Cost center</th>
                                <th scope="col" class="border-r border-b  dark:border-neutral-400 py-4">Currency</th>
                                <th scope="col" class=" border-r border-b  dark:border-neutral-400 py-4">rate</th>
                                <th v-for="(field,index) in customfields" :key="index"  scope="col" class="py-4 border-2 dark:border-neutral-400">
                                  {{ field }}  
                                </th>
                                </tr>
                            </thead>
                            
                            <tbody> 
                                <tr v-for="(i,index) in rows_count " :key="index" ref="rows" class=" odd:bg-white even:bg-slate-200 dark:border-neutral-500 dark:odd:bg-gray-800 dark:even:bg-gray-700  ">
                                  <td class="sticky left-0 bg-inherit z-10  text-center font-medium border-r  border-gray-400">
                                      <div class=" w-full py-3 px-1  border-gray-400 ">{{index+1}}</div>                    
                                  </td>

                                  <td class="whitespace-nowrap   border-gray-400 ">
                                    <ccc  v-model="form[index].debit_amount"   @change="remove_credit_amount(index)"  
                                    :TableObject="TableObject"  :rows_index="index" :columns_index=1  Format="number" />
                                  </td>

                                  <td class="whitespace-nowrap   border-gray-400 ">
                                    <ccc  v-model="form[index].credit_amount"   @change="remove_debit_amount(index)" 
                                    :TableObject="TableObject"  :rows_index="index" :columns_index=2  Format="number" />                                 
                                  </td>
                                  
                                  <td class="whitespace-nowrap  border-gray-400   ">                         
                                    <ccc v-model="form[index].account"   @change="form_have_been_adjusted=true" :TableObject="TableObject"  :rows_index="index" :columns_index=3
                                    Format="aoutcomplete" :SearchFunction="searchStore.search_account" :Suggestions="searchStore.available_accounts.value"  >  
                                      <template #emptySuggestions>
                                        <div class=""> account <span class="text-blue-600">{{form[index].account }}</span> dose not exist </div>
                                        <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                                      </template>
                                    </ccc>
                                  </td>

                                  <td class="whitespace-nowrap border-gray-400  ">
                                    <ccc  v-model="form[index].description"  @change="form_have_been_adjusted=true" 
                                    :TableObject="TableObject"  :rows_index="index" :columns_index=4  Format="text" />
                                  </td>

                                  <td class="whitespace-nowrap border-r border-gray-400">
                                    <ccc v-model="form[index].cost_center" :TableObject="TableObject"  :rows_index="index" :columns_index=5
                                    Format="aoutcomplete" :SearchFunction="searchStore.search_cost_center" 
                                    :Suggestions="searchStore.available_cost_centers.value" >
                                      <template #emptySuggestions>
                                        <div class=""> cost center <span class="text-blue-600">{{form[index].cost_center }}</span> dose not exist </div>
                                        <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                                      </template>
                                    </ccc>
                                  </td>

                                  <td class="whitespace-nowrap  border-gray-400  dark:text-gray-200 text-gray-800 ">
                                    <ccc v-model="form[index].currency" :TableObject="TableObject" :rows_index="index" :columns_index=6
                                    @UpdateCurrencyRate="(rate)=>form[index].currency_rate=rate" 
                                    Format="aoutcomplete" :SearchFunction="searchStore.search_currencey" :Suggestions="searchStore.filterd_currencies.value" >  
                                      <template #emptySuggestions>
                                        <div class=""> currency <span class="text-blue-600">{{form[index].currency }}</span> dose not exist </div>
                                      </template>
                                    </ccc>
                                  </td>

                                  <td :class="{'text-transparent': form[index].currency_rate==1}" class="whitespace-nowrap  border-gray-400  " >
                                    <ccc v-model="form[index].currency_rate"  :TableObject="TableObject"  :rows_index="index" :columns_index=7
                                    :Default ="form[index].currency?.default_rate"  Format="number" 
                                    />
                                  </td>

                                  <td v-for="(field,failed_index) in customfields" :key="failed_index" class="whitespace-nowrap border border-gray-400 ">
                                    <ccc  v-model="form[index].customfields[field]" 
                                    :TableObject="TableObject"  :rows_index="index" :columns_index=8  Format="text" />
                                  </td>

                                  <td class="whitespace-nowrap border border-gray-400">

                                    
                                  </td>
                                </tr>
                            </tbody>
                            
                    </table>
                </div>

                <div class=" w-60 inline-flex justify-around bg-sky-200  ">
                  <div class="">Total</div>

                    <div class="">{{ Entry_Totals.debit_side }}</div>
                    <div class="" >{{ Entry_Totals.credit_side }}</div>
                </div>
              <!-- buttons --> 
              <div  class="flex justify-end w-9/12  my-0.5 float-right  mr-9">
                <Link @click.prevent="console.log(form_have_been_adjusted)" href="/create_entry/general_entry/documents" class=" p-2 mx-4 font-semibold rounded-md bg-black text-white "  >New  {{ form_have_been_adjusted }}</Link>
                <button @click="update_document" class=" p-2 mx-4 font-semibold rounded-md bg-blue-600 text-white "   >Update</button>
                <div @click="DeleteModal=true" class=" p-2 mx-4 font-semibold rounded-md bg-red-600 text-white "   >Delete</div>
                <button class=" p-2 mx-4 font-semibold rounded-md bg-blue-600 text-white "   >Print</button>
                <button class=" p-2 mx-4 font-semibold rounded-md bg-blue-600 text-white "  type="submit" >save</button>

              </div>
            </form>
            <div   class="clear-both"></div>
            <Toast 
              :pt="{ 
                    root:{class: 'opacity-95'},
                    content: { class:severity_style ,},
                    icon:{class: 'stroke-white fill-white'},
                }"
            />
        </div>
        <!-- delete modal --> 
        <ConfirmationModal :show="DeleteModal" @close="DeleteModal = false">
          <template #title>
              Delete Account
          </template>

          <template #content>
              Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted.
          </template>

          <template #footer>
              <SecondaryButton class="p-2 mx-4 font-semibold rounded-md"   @click="DeleteModal = false">
                  Nevermind
              </SecondaryButton>

              <DangerButton class="p-2 mx-4 font-semibold rounded-md" @click="delete_document"  :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Delete Document
              </DangerButton>
          </template>
        </ConfirmationModal>



    </AppLayout>
</template>