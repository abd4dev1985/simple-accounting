<script setup>

import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive ,computed,watch,onUpdated,ref,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../searchStore.vue';
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

let props =defineProps({
    entry_lines:{ type: Array , default:[] }   ,
    document_catagory:{   }   ,
    accounts:{},
    document : {        },
    new_document_number:{     },
    invoice_lines:{type:Array,default:[]},
    invoice_type:{},
    entry_lines:{type:Array,default:[]},
    cash_account:{},
    default_account:{},
    columns_count:{},
    customfields:{type:Array },
    currencies : { type: Array , default:[]   },
    operation :{ type:String , default: 'update'  },
    delete_url:{},
    update_url:{},
    store_url:{},
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
 const errors = computed(() => page.props.errors)
const currencies= (page.props.currencies)? page.props.currencies:[];

let document_number =ref( (props.document)? props.document.number: props.new_document_number );
let document_date = ref( (props.document)? props.document.date : new Date()  )

searchStore.available_currencies.value = currencies

let form_have_been_adjusted = ref(false);



let DeleteModal = ref(false)

let default_account=ref(props.default_account)
let Client_Or_Vendor_Account= ref('') 
let  PaymentMethod = ref({name:'cash'});
let AvailablePaymentMethod=ref([{name:'cash'},{name:'credit'}])

let Invoice_Lines = ref ( ( props.invoice_lines)? props.invoice_lines :[] )  ;
let Etry_Lines=ref( (props.entry_lines)? props.entry_lines :[] )
let rows_count = 30 + Invoice_Lines.value.length;

// create array from the lines of receipt (form)
let lines=[]
if ( !Etry_Lines.value[0]) {
  Etry_Lines.value[0]={
    debit_amount:null,credit_amount:null,account:props.cash_account,description:null,
    currencey:null,currency_rate:1,cost_center:null , customfields: get_standard_object(),
  }
}
for (let index = 0; index < rows_count; index++) {
  if ( !Invoice_Lines.value[index]) {
    Invoice_Lines.value[index]={
      quantity:null,price:null,product:null,ammount:null,description:null,currencey:null,
      currency_rate:1, cost_center:null , customfields: get_standard_object(),
    }
  }

  if (!Etry_Lines.value[index] && index !=0 ) {
    Etry_Lines.value[index]={
      debit_amount:null,credit_amount:null,account:null,description:null,
      currencey:null,currency_rate:1,cost_center:null , customfields: get_standard_object(),
    }  
  }
}

console.log([Etry_Lines.value,Invoice_Lines.value])
watch([PaymentMethod,Client_Or_Vendor_Account],([NewPaymentMethod,New_Client_Or_Vendor_Account])=>{
  if (NewPaymentMethod.name=='cash') {
    Client_Or_Vendor_Account.value=null
    Etry_Lines.value[0].account = props.cash_account
  }else{
    Etry_Lines.value[0].account = New_Client_Or_Vendor_Account
  }
})

let form = ref(lines)

const rows = ref([])
const scrollable_table = ref()

let column_index=0

const tableHeader=ref()

const Totals_Ammount =computed(()=>{
  let total=0
  Invoice_Lines.value.forEach(line => {
    total +=isNaN(line.ammount)?0:line.ammount
  });
  if (props.invoice_type=='purchase') {
    Etry_Lines.value[0].credit_amount=total
  }
  if (props.invoice_type=='sale') {
    Etry_Lines.value[0].debit_amount=total
  }
  
  return total
});


let TableObject =computed(()=>{
  return {
    Table:scrollable_table.value ,TableHeader:tableHeader.value,Rows:rows.value,
    CollumnsCount:props.columns_count + props.customfields.length}
})

//inforce value of an element inside array to be a number  
function format_number(value){
let formatter =Intl.NumberFormat('en')
if (  !isNaN(Number(value))  &&  Number(value) != 0    ) {
    return formatter.format(value)
  }else{  
    return null 
  }
}

function get_ammount(index){
  Invoice_Lines.value[index].ammount=Invoice_Lines.value[index].price*Invoice_Lines.value[index].quantity
  if (props.invoice_type=='purchase' && !isNaN(Invoice_Lines.value[index].ammount)  ) {
    Etry_Lines.value[index+1].debit_amount=Invoice_Lines.value[index].ammount 
    Etry_Lines.value[index+1].account=default_account.value

  }
  if (props.invoice_type=='sale' && !isNaN(Invoice_Lines.value[index].ammount)) {
    Etry_Lines.value[index+1].credit_amount=Invoice_Lines.value[index].ammount 
    Etry_Lines.value[index+1].account=default_account.value
  }

}

function clear_form(){
  PaymentMethod.value={name:'cash'}
  Etry_Lines.value[0]={
    debit_amount:null,credit_amount:null,account:props.cash_account,description:null,
    currencey:null,currency_rate:1,cost_center:null , customfields: get_standard_object(),
  }
  rows_count = 30 
  for(let index = 0; index < rows_count; index++) {
    Invoice_Lines.value[index]={
      quantity:null,price:null,product:null,ammount:null,description:null,currencey:null,
      currency_rate:1, cost_center:null , customfields: get_standard_object(),
    }
    Etry_Lines.value[index]={
      debit_amount:null,credit_amount:null,account:null,description:null,
      currencey:null,currency_rate:1,cost_center:null , customfields: get_standard_object(),
    }  
  }

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

function  convert_date_to_sting(date){
  if ( typeof date == 'string') {
    return date
  }else{ return date.toJSON().slice(0,10)                  }
}

function update_document() {
  let data={
   
    document_number:document_number.value ,
    default_account:default_account.value,
    PaymentMethod:PaymentMethod.value.name,
    Client_Or_Vendor_Account:Client_Or_Vendor_Account.value,
    entry_lines:Etry_Lines.value,
    document_catagory_id:page.props.document_catagory.id ,
    lines:Invoice_Lines.value ,
    date:convert_date_to_sting(document_date.value),
  }

  router.put(props.update_url, {
    document_number:document_number.value ,
    default_account:default_account.value,
    PaymentMethod:PaymentMethod.value.name,
    Client_Or_Vendor_Account:Client_Or_Vendor_Account.value,
    entry_lines:Etry_Lines.value,
    document_catagory_id:page.props.document_catagory.id ,
    lines:Invoice_Lines.value ,
    date:convert_date_to_sting(document_date.value),
    }
    ,{onError:(errors)=>{
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
  router.post(props.store_url,{
    document_number:document_number.value ,
    default_account:default_account.value,
    PaymentMethod:PaymentMethod.value.name,
    Client_Or_Vendor_Account:Client_Or_Vendor_Account.value,
    entry_lines:Etry_Lines.value,
    document_catagory_id:page.props.document_catagory.id ,
    lines:Invoice_Lines.value ,
    date:convert_date_to_sting(document_date.value),
  },{
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

          <div class="flex flex-wrap gap-y-5 gap-x-5 tab:gap-x-8     tab:flex-row dark:text-gray-200 justify-between mx-3 my-4 ">
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

             <!-- payment method Input   -->
             <div class="flex-initial ">
              <label class="block text-xs py-0.5 font-semibold text-left" for="">payment method</label>
              <Dropdown v-model="PaymentMethod" :options="AvailablePaymentMethod"
                 optionLabel="name"  
                :pt="{
                    root:{
                      class:'h-8 dark:bg-gray-700 dark:text-gray-200  ',
                    },
                    input: {
                      class: ' p-0 text-center w-14  dark:text-gray-200  ',
                      form : 'myform' ,
                    },
                    trigger:{
                      class:' dark:text-gray-200',
                    },
                  }">
              </Dropdown>
            </div>
            

             <!-- client or vendor Account Input   -->
             <div class="flex-initial ">
              <label class="block text-sm font-semibold text-left" for="">
                Vendor Account 
              </label>
              <AutoComplete v-model="Client_Or_Vendor_Account" :suggestions="searchStore.available_accounts.value"
                :class="{'p-invalid':errors.Client_Or_Vendor_Account}" @complete="searchStore.search_account"
                optionLabel="name" forceSelection :disabled="PaymentMethod.name=='cash'"
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

           
            
             <!-- client or vendor Account Input   -->
             <div class="flex-initial ">
              <label class="block text-sm font-semibold text-left " for="">
                Vendor Account
              </label>
              <AutoComplete v-model="Client_Or_Vendor_Account" :suggestions="searchStore.available_accounts.value"
                @complete="searchStore.search_account" optionLabel="name" forceSelection :class="{'p-invalid':errors.Client_Or_Vendor_Account}"
                :pt="{
                  //  root:{
                    //  class:(errors.Client_Or_Vendor_Account)?'border-2 border-red-400':null,
                    //},
                    input: {
                      class: 'bg-white h-8 w-44 py-2 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
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
              <label class="block text-sm font-semibold text-left" for="">Date </label>
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
               <div ref="scrollable_table"  class=" lg:h-[410px] mx-6 relative  overflow-auto scrollbar larg:max-w-[73vw]     " >
                    <table class=" dark:text-gray-200   text-center border-collapse text-sm font-light">
                        
                            <thead ref="tableHeader" class="sticky top-0   z-[20] dark:bg-gray-700 bg-white border-b-2 font-medium dark:border-neutral-500">
                                <tr >
                                <th scope="col" class=" py-4 sticky  z-[10] left-0   bg-gray-200 ">#</th>
                                <th scope="col" class=" py-4">Product</th>
                                <th scope="col" class="py-4"> Quantity</th>
                                <th scope="col" class=" ">Price</th>
                                <th scope="col" class=" py-4">Ammount</th>
                                <th scope="col" class=" block overflow-auto resize-x py-4 min-w-[200px] ">Description</th>
                                <th scope="col" class=" py-4">Currencey</th>
                                <th scope="col" class=" py-4">rate</th>
                                <th scope="col" class=" py-4">cost center</th>
                                <th v-for="(field,index) in customfields" :key="index"  scope="col" class="py-4">{{ field }}  </th>
                                </tr>
                            </thead>
                            
                            <tbody> 
                                <tr v-for="(line,index) in Invoice_Lines " :key="index" ref="rows" class=" odd:bg-white even:bg-slate-200 dark:border-neutral-500 dark:odd:bg-gray-800 dark:even:bg-gray-700 text-base font-medium ">
                                  <td class="sticky left-0 bg-inherit z-10  text-center font-medium border  border-gray-400">
                                      <div class=" w-full py-3 px-1 border-r border-gray-400 ">{{index+1}}</div>                    
                                  </td>

                                  <td class="whitespace-nowrap border border-gray-400   ">                         
                                    <ccc v-model="line.product"  Format="aoutcomplete" :Invalid="errors['lines.'+index+'.product']"
                                    @change="form_have_been_adjusted=true" :TableObject="TableObject"  :rows_index="index" :columns_index=1
                                    :SearchFunction="searchStore.search_product" :Suggestions="searchStore.available_products.value" >  
                                      <template #emptySuggestions>
                                        <div class=""> product <span class="text-blue-600">{{line.product }}</span> dose not exist </div>
                                        <Link :href="searchStore.create_new_product_link.value" class="text-blue-600"> create new one</Link>
                                      </template>
                                    </ccc>
                                  </td>
                                  

                                  <td class="whitespace-nowrap border border-gray-400 text "      >
                                    <ccc  v-model="line.quantity"  @change="get_ammount(index)" :Invalid="errors['lines.'+index+'.quantity']"
                                    :TableObject="TableObject"  :rows_index="index" :columns_index=2  Format="number" />
                                  </td>

                                  <td class="whitespace-nowrap  border border-gray-400  ">
                                    <ccc  v-model="line.price" @change="get_ammount(index)" :Invalid="errors['lines.'+index+'.price']"
                                    :TableObject="TableObject"  :rows_index="index" :columns_index=3  Format="number" />                                 
                                  </td>
                                  
                                  <td class="whitespace-nowrap border border-gray-400   ">                         
                                    <ccc  v-model="line.ammount" :ReadOnly="true" @change="console.log('ammount changed')"
                                    :TableObject="TableObject"  :rows_index="index" :columns_index=4  Format="number" />
                                  </td>

                                  <td class="whitespace-nowrap border border-gray-400 ">
                                    <ccc  v-model="lines.description"  @change="form_have_been_adjusted=true" 
                                    :TableObject="TableObject"  :rows_index="index" :columns_index=5  Format="text" />
                                  </td>

                                  <td class="whitespace-nowrap border border-gray-400 ">
                                    <ccc v-model="line.currencey" :TableObject="TableObject" :rows_index="index" :columns_index=6
                                    @UpdateCurrencyRate="(rate)=>line.currency_rate=rate"  :Default="currencies[0]"  
                                    Format="aoutcomplete" :SearchFunction="searchStore.search_currencey" :Suggestions="searchStore.filterd_currencies.value" >  
                                      <template #emptySuggestions>
                                        <div class=""> currencey <span class="text-blue-600">{{form[index].currencey }}</span> dose not exist </div>
                                      </template>
                                    </ccc>
                                  </td>

                                  <td :class="{'text-transparent': line.currency_rate==1}" class="whitespace-nowrap border border-gray-400 " >
                                    <ccc v-model="line.currency_rate"  :TableObject="TableObject"  :rows_index="index" :columns_index=7 
                                    :Default ="line.currencey?.default_rate"  Format="number" 
                                    />
                                  </td>

                                  <td class="whitespace-nowrap border border-gray-400">
                                    <ccc v-model="line.cost_center" :TableObject="TableObject"  :rows_index="index" :columns_index=8
                                    Format="aoutcomplete" :SearchFunction="searchStore.search_cost_center"
                                    :Suggestions="searchStore.available_cost_centers.value" >
                                      <template #emptySuggestions>
                                        <div class=""> cost center <span class="text-blue-600">{{line.cost_center }}</span> dose not exist </div>
                                        <Link :href="searchStore.create_new_account_link.value" class="text-blue-600"> create new one</Link>
                                      </template>
                                    </ccc>
                                  </td>

                                  <td v-for="(field,failed_index) in customfields" :key="failed_index" class="whitespace-nowrap border border-gray-400 ">
                                    <ccc  v-model="line.customfields[field]" 
                                    :TableObject="TableObject"  :rows_index="index" :columns_index=9  Format="text" />
                                  </td>

                                  <td class="whitespace-nowrap border border-gray-400">

                                    
                                  </td>
                                </tr>
                            </tbody>
                            
                    </table>
                </div>

                <div class=" w-60 inline-flex justify-around bg-sky-200  ">
                  <div class="">Total ammount</div>

                    <div class="">{{ format_number(Totals_Ammount) }}</div>
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