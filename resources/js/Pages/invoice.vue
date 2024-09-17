<script setup>

import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive ,computed,watch,onUpdated,ref,onMounted,  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../searchStore.vue';
import DateObject from '../DateObject.vue';
import MoblieInvoiceTable from '@/Pages/MoblieInvoiceTable.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ConfirmationModal  from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import DangerButton from '@/Components/DangerButton.vue';    
import ccc from '@/Components/ccc.vue';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import Invoice from '../InvoiceLines.vue';
import {Create_Invoice_Line,Create_Entry_Line} from '../EntryInvoiceLines.vue';
import { useWinBox } from 'vue-winbox'

// console.log(Setup_Lines)
const toast = useToast();
let screenWidth=ref(0);
screenWidth.value= document.getElementById("app").offsetWidth
let Device_is_Mobile = ref(screenWidth.value <=800?true:false )

let severity_style= ref('');

onMounted(() => {
    if (window !== undefined) {
        window.addEventListener('resize', ()=>{
        screenWidth.value= document.getElementById("app").offsetWidth
        Device_is_Mobile.value = screenWidth.value <=800?true:false 

        })
    }
})

let props =defineProps({
    entry_lines:{ type: Array , default:[] }   ,
    document_catagory:{   }   ,
    accounts:{},
    document : {        },
    last_document: {  } ,
    new_document_number:{     },
    currency_id:{},
    Invoice_Currency_Rate:{},
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
})

let document_number =ref( (props.document)? props.document.number: props.new_document_number );

let pervious_document_url = computed(()=>{
  if (props.document ) {
    return route(props.invoice_type +'.pervious',{document_catagory:props.document_catagory.name ,document:props.document.number})
  }
  if (props.last_document  ) {
    return route(props.invoice_type +'.show',{document_catagory:props.document_catagory.name ,document:props.last_document.number})
  }
  return ""
 })

 let next_document_url = computed(()=>{
  if (props.document ) {
    return route(props.invoice_type +'.next',{document_catagory:props.document_catagory.name ,document:props.document.number})
  }
  return ""
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

let document_date = ref( (props.document)? props.document.date : new Date() )

searchStore.available_currencies.value = currencies

let form_have_been_adjusted = ref(false);

let DeleteModal = ref(false)

let default_account=ref(props.default_account)
let Client_Or_Vendor_Account= ref('') 
let  PaymentMethod = ref({name:'cash'});

let AvailablePaymentMethod=ref([{name:'cash'},{name:'credit'}])

 let NewInvoiceLink = computed(()=>{
  if (props.invoice_type == 'purchase') {
    return route('purchase.create',props.document_catagory.name)
  }
  if (props.invoice_type == 'sale') {
    return route('sale.create',props.document_catagory.name)
  }
 })

function select_currency(){
  for (let index = 0; index < currencies.length; index++) {
    if (currencies[index].id==props.currency_id) {
        return { id:currencies[index].id , name:currencies[index].name }
    }  
  }
  return { id:currencies[0].id , name:currencies[0].name }
}

let Invoice_Currency =ref( select_currency() )
let Invoice_Currency_Rate =ref((props.document)?  props.Invoice_Currency_Rate  :1  )

// create copy of invoce lines props
let Copy_Invoice_Lines = props.invoice_lines.map((obj) =>{ 
  obj.customfields = JSON.parse(obj.customfields)
  return {...obj} 
});
let Invoice_Lines = ref ( );
Invoice_Lines.value = Copy_Invoice_Lines ;


let rows_count = 15 + Invoice_Lines.value.length;

// create array from the lines of receipt (form)

for (let index = 0; index < rows_count; index++) {
  if ( !Invoice_Lines.value[index]) {
    Invoice_Lines.value[index]={
      quantity:null,price:null,product:null,ammount:null,description:null,currency:Invoice_Currency.value,
      currency_rate:1, cost_center:null , customfields: get_standard_object(),
    }
  }
}

// add more lines(products) to invoice  
function Add_Lines(){
  Invoice_Lines.value.push({
    quantity:null,price:null,product:null,ammount:null,description:null,currency:Invoice_Currency.value,
    currency_rate:1, cost_center:null , customfields: get_standard_object(),
  })
}


watch([PaymentMethod,Client_Or_Vendor_Account],([NewPaymentMethod,New_Client_Or_Vendor_Account])=>{
  if (NewPaymentMethod.name=='cash') {
    Client_Or_Vendor_Account.value=null
  }else{
  }
})

watch([Invoice_Currency,Invoice_Currency_Rate],([New_Invoice_Currency,New_Invoice_Currency_Rate])=>{
  for (let index = 0; index < Invoice_Lines.value.length; index++) {
    Invoice_Lines.value[index].currency=New_Invoice_Currency
    Invoice_Lines.value[index].currency_rate=New_Invoice_Currency_Rate
  }

})
 Invoice_Currency.value=select_currency()
//debugger;
const rows = ref([])
const scrollable_table = ref()
let column_index=0

const tableHeader=ref()
// calculate invoice total ammount , also calculate total credi or debit ammount for entry
const Totals_Ammount =computed(()=>{
  let total = 0
  Invoice_Lines.value.forEach(line => {
    if ( !isNaN( line.ammount )) {
      total = total + Number(line.ammount)
    }
  });
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
}

function clear_form(){
  PaymentMethod.value={name:'cash'}
  rows_count = 30 
  for(let index = 0; index < rows_count; index++) {
    Invoice_Lines.value[index]={
      quantity:null,price:null,product:null,ammount:null,description:null,currency:null,
      currency_rate:1, cost_center:null , customfields: get_standard_object(),
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

function update_document() {
  router.put(props.update_url, {
    document_number:document_number.value ,
    default_account:default_account.value,
    PaymentMethod:PaymentMethod.value.name,
    Client_Or_Vendor_Account:Client_Or_Vendor_Account.value,
    document_catagory_id:page.props.document_catagory.id ,
    lines:Invoice_Lines.value ,
    date:DateObject.ToString(document_date.value) ,
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
  let x = Invoice_Lines.value.filter( (line)=>{
    return !(line.prouct==null && line.price==null && line.quantity ==null)
  } )
  router.post(props.store_url,
      {
        document_number:document_number.value ,
        default_account:default_account.value,
        PaymentMethod:PaymentMethod.value.name,
        Client_Or_Vendor_Account:Client_Or_Vendor_Account.value,
        document_catagory_id:page.props.document_catagory.id ,
        lines:x ,
        date:DateObject.ToString(document_date.value) ,
      },
      {
        onError:(errors)=>{
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
const dt = ref();
const exportCSV = () => { dt.value.exportCSV()}

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
              
              <Link  v-if="next_document_url && operation=='update'" :href="next_document_url"  >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-9 h-9 "><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>
              </Link>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="#d1d5db" viewBox="0 0 24 24" stroke-width="1.5" stroke="#d1d5db" class="w-9 h-9 "><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>
            </div>
          </h1>
          <!-- header inputs like enrtry no,payment method,date and Currency  -->
          <div class="flex mobile:flex-col flex-wrap mobile:gap-y-1 gap-y-5 gap-x-8      tab:flex-row dark:text-gray-200 justify-between mx-3 my-4 ">
            <!-- INPUT DOCUMENT NUMBER -->
            <div class="flex-initial  w-max">
              <div class="text-center relative">
                <label class="block font-semibold text-left text-sm dark:text-gray-200 text-black " for="document_no">Invoice Number </label>
                <input v-model="document_number" id="document_no" form="myform" class="block border text-center rounded-md w-14 h-8 text-gray-700 " >
                <!-- 
                <div v-if="errors.receipt_id" class="   text-red-500">{{ errors.receipt_id}}</div>
                -->
              </div>
            </div>
               <!-- DATE INPUT   -->
              <div class="flex-initial ">
                <label class="block text-sm font-semibold text-left" for="">Date </label>
                <Calendar v-model="document_date" showIcon  dateFormat="dd/mm/yy"
                  :pt="{
                      root:{class:' w-full dark:bg-gray-700'},
                      input: { 
                        class: 'bg-white text-center h-8  w-32 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        form:'myform',
                      },
                      dropdownButton: {
                        root: { class: 'h-8 bg-sky-800' }
                      }
                  }"
                />
              </div>

             <!-- payment method Input   -->
             <div class="flex-initial ">
              <label class="block text-sm py-0.5 font-semibold text-left" for="">payment method</label>
              <Dropdown v-model="PaymentMethod" :options="AvailablePaymentMethod"
                 optionLabel="name"  
                :pt="{
                    root:{
                      class:'h-8 dark:bg-gray-700 mobile:w-full dark:text-gray-200  ',
                    },
                    input: {
                      class: 'p-0 text-center w-14  dark:text-gray-200',
                      form : 'myform' ,
                    },
                    trigger:{
                      class:'dark:text-gray-200',
                    },
                  }">
              </Dropdown>
            </div>
            

             <!-- client or vendor Account Input   -->
             <div v-show="PaymentMethod.name !='cash' " class=" ">
              <label  class="block text-sm font-semibold text-left"  for="">
                <span v-if="invoice_type=='purchase'"  >Vendor</span>
                <span v-if="invoice_type=='sale'"  >Customer</span>
                Account 
              </label>
              <AutoComplete v-model="Client_Or_Vendor_Account" :suggestions="searchStore.available_accounts.value"
                :class="{'p-invalid':errors.Client_Or_Vendor_Account}" @complete="searchStore.search_account"
                optionLabel="name" forceSelection :disabled="PaymentMethod.name=='cash'"
                :pt="{
                    root:{
                        class:'h-8 dark:bg-gray-700 mobile:w-full dark:text-gray-200  ',
                    },
                    input: {
                      class: 'bg-white h-8 w-44 mobile:w-full py-2   dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
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
            <div class="mobile:hidden ">
              <label class="block text-sm font-semibold text-left" for="">Default Account</label>
              <AutoComplete v-model="default_account" :suggestions="searchStore.available_accounts.value"
                @complete="searchStore.search_account" optionLabel="name" forceSelection 
                :pt="{
                    root:{
                        class:'h-8 dark:bg-gray-700 mobile:w-full  dark:text-gray-200  ',
                    },
                    input: {
                      class: 'bg-white h-8  mobile:w-full py-2   dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
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

             <!-- INVOICE CURRENCY INPUT  -->
            <div class=" mobile:flex justify-around">
              <div>
                <label class="block self-start text-sm font-semibold text-left" for="">
                  Currency
                </label>
                <AutoComplete v-model="Invoice_Currency" :suggestions="searchStore.filterd_currencies.value"  
                  @complete="searchStore.search_currencey" optionLabel="name" forceSelection
                  @item-select="Invoice_Currency_Rate  =Invoice_Currency.default_rate"
                  :pt="{
                      input: {
                        class: 'bg-white h-8 w-24 py-2 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                        form : 'myform' ,
                      },
                    }">
                    <template #empty>
                        <div   class="font-semibold p-3 border-2 border-blue-500">
                            <div class=""> currency <span class="text-blue-600">{{Invoice_Currency }}</span> dose not exist </div>
                            <Link :href="searchStore.create_new_currencey_link.value" class="text-blue-600"> create new one</Link>
                        </div>
                    </template> 
                </AutoComplete>
              </div>
              <!-- INVOICE CURRENCY RATE INPUT for mobile  -->
              <div class="tab:hidden">
                <div class="text-center relative">
                  <label class="block font-semibold text-left text-sm dark:text-gray-200 text-black " for="document_no"> Currency Rate </label>
                  <input v-model="Invoice_Currency_Rate" id="document_no" form="myform" class="block border text-center mx-auto rounded-md w-14 h-8 text-gray-700 " >
                </div>
              </div>
            </div>
            <!-- INVOICE CURRENCY RATE INPUT for pc   -->
            <div class="mobile:hidden flex-initial  w-max">
              <div class="text-center relative">
                <label class="block font-semibold text-left text-sm dark:text-gray-200 text-black " for="document_no"> Currency Rate </label>
                <input v-model="Invoice_Currency_Rate" id="document_no" form="myform" class="block border text-center mx-auto rounded-md w-14 h-8 text-gray-700 " >
              </div>
            </div>


          </div>
            <form class="sm:-mx-1 lg:-mx-2 h-screen flex flex-col " id="myform" @submit.prevent="submit">
               <!-- invoice table for mobile   -->
               <div v-if="Device_is_Mobile" class="mx-3 mt-7"  >
                  <h1>ITEMS</h1>
                  <div v-for="(line,index) in Invoice_Lines"  :key="index" > 
                      <MoblieInvoiceTable  v-model:line="Invoice_Lines[index]" @change="get_ammount(index)" :default_line="props.invoice_lines[index]"  >

                      </MoblieInvoiceTable>
                  </div>
                  <MoblieInvoiceTable  v-model:line="Invoice_Lines[Invoice_Lines.length-1]" @New_Line_Added="Add_Lines()" @change="get_ammount(Invoice_Lines.length-1)" v-slot="slotProps">
                    <div @click="slotProps.open_Product_Modal" class="font-semibold text-green-600 mt-4 ">  + Add Product</div>
                  </MoblieInvoiceTable>
                  
               </div>
               <!-- invoice table for desktop  -->
               <div v-if="!Device_is_Mobile"   ref="scrollable_table"  class=" flex-initial  h-[45vh] mobile:h-[30vh]  mx-6  relative  overflow-auto scrollbar larg:max-w-[73vw]     " >
                    <table class=" dark:text-gray-200   text-center border-collapse text-sm font-light">
                        
                            <thead ref="tableHeader" class="sticky top-0   z-[20] dark:bg-gray-700 bg-white border-b-2 font-medium dark:border-neutral-500">
                                <tr >
                                <th scope="col" class=" py-4 sticky  z-[10] left-0   bg-gray-200 ">#</th>
                                <th scope="col" class=" py-4">Product</th>
                                <th scope="col" class="py-4"> Quantity</th>
                                <th scope="col" class=" ">Price</th>
                                <th scope="col" class=" py-4">Ammount</th>
                                <th scope="col" class=" block overflow-auto resize-x py-4 min-w-[200px] ">Description</th>
                                <th scope="col" class=" py-4">Currency</th>
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
                                    <ccc  v-model="line.description"  @change="form_have_been_adjusted=true" 
                                    :TableObject="TableObject"  :rows_index="index" :columns_index=5  Format="text" />
                                  </td>

                                  <td class="whitespace-nowrap border border-gray-400 " :class="{'text-transparent': line.currency_rate==1}"   >
                                    <ccc v-model="line.currency" :TableObject="TableObject" :rows_index="index" :columns_index=6
                                    @UpdateCurrencyRate="(rate)=>line.currency_rate=rate"   
                                    Format="aoutcomplete" :SearchFunction="searchStore.search_currencey" :Suggestions="searchStore.filterd_currencies.value" >  
                                      <template #emptySuggestions>
                                        <div class=""> currency <span class="text-blue-600">{{form[index].currency }}</span> dose not exist </div>
                                      </template>
                                    </ccc>
                                  </td>

                                  <td :class="{'text-transparent': line.currency_rate==1}" class="whitespace-nowrap border border-gray-400 " >
                                    <ccc v-model="line.currency_rate"  :TableObject="TableObject"  :rows_index="index" :columns_index=7 
                                    :Default ="line.currency?.default_rate"  Format="number" 
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
                                    <td></td>
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
                
              <!-- buttons --> 
              <div  class="flex-none grid justify-start grid-cols-5 tab:grid-cols-10 items-start  h-[20vh] space-x-5 mobile:space-x-2 w-full gap-y-5  my-3  ">
                
                <button class="block mobile:col-span-2 ml-5 py-2 px-4 font-semibold rounded-md bg-sky-700 text-white min-w-max "  type="submit" >
                   <span v-if="operation=='create'" >save </span>  <span v-else="operation=='create'" >Update </span>
                </button>
                
                <div @click="DeleteModal=true" class="mobile:col-span-2 p-2 min-w-max font-semibold rounded-md bg-red-600 text-white text-center "   >Delete</div>
                
                <button @click="update_document" class="mobile:col-span-2 p-1.5 min-w-max font-semibold rounded-md text-gray-900 border-2 border-gray-600"   >Share</button>

                <Link  :href="NewInvoiceLink" class="block mobile:col-span-2 min-w-max p-2 font-semibold rounded-md bg-black text-white text-center "  >
                  New  
                </Link>

                <button @click="exportCSV($event)"  class="block mobile:col-span-2 min-w-fit p-2  font-semibold rounded-md bg-white text-gray-800 border-2 px-4 border-gray-600"   >
                  Export
                </button>

                <div class="col-span-5 tab:order-none order-first  font-bold text-right ">
                  <div class="tab:ml-[40%]  p-2 rounded-md bg-white text-sky-950 w-max ring-1 shadow-2xl " >
                    Total ammount <span class=" mx-4">{{Totals_Ammount?format_number(Totals_Ammount):0 }}</span>
                  </div>
                </div>

              </div>
            </form>

            <Toast 
              :pt="{ 
                    root:{class: 'opacity-95'},
                    content: { class:severity_style ,},
                    icon:{class: 'stroke-white fill-white'},
                }"
            />
        </div>
        
        <!-- export invoice excel --> 
        <div class="hidden">
          <DataTable :value="Invoice_Lines" ref="dt">
            
            <Column field="price" header="price" >
            </Column>
            <Column field="ammount" header="ammount" >
            </Column>

          </DataTable>
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

              <DangerButton class="p-2 mx-4 font-semibold rounded-md" @click="delete_document" >
                  Delete Document
              </DangerButton>
          </template>
        </ConfirmationModal>



    </AppLayout>
</template>