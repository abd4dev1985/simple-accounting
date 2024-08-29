<script setup>

import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive ,computed,watch,onUpdated,ref,onMounted  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../../searchStore.vue';
import DateObject from '../../DateObject.vue';
import Language from '@/Pages/Language.vue';
import EntriesTableForDesktop from '@/Pages/JournalEntry/EntriesTableForDesktop.vue';
import EntriesTableForMobile from '@/Pages/JournalEntry/EntriesTableForMobile.vue';
import CreateEntryLines from '@/Pages/CreateEntryLines.vue';
import ConfirmationModal  from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue'; 
import DangerButton from '@/Components/DangerButton.vue';    
import Toast from 'primevue/toast';
import ccc from '@/Components/ccc.vue';
import { useToast } from "primevue/usetoast";
import html2pdf  from "html2pdf.js";

const toast = useToast();
let screenWidth=ref(0);
screenWidth.value= document.getElementById("app").offsetWidth
let Device_is_Mobile = ref(screenWidth.value <=800?true:false )
let html_to_pdf = ref()


onMounted(() => {
    if (window !== undefined) {
        window.addEventListener('resize', ()=>{
        screenWidth.value= document.getElementById("app").offsetWidth
        Device_is_Mobile.value = screenWidth.value <=800?true:false 
        })
    }
})

let download_pdf =function(){
  var worker = html2pdf().from(html_to_pdf.value).save();
 // html2pdf(html_to_pdf.value,{filename:"test.pdf"}) 
}

let severity_style= ref('');
let Translate = Language.Translate

let props =defineProps({
    entry_lines:{ type: Array , default:[] }   ,
    document_catagory:{   }   ,
    accounts:{},
    document : {        },
    last_document: {  } ,
    new_document_number:{     },
    columns_count:{},
    customfields:{type:Array },
    currencies : { type: Array , default:[]   },
    operation :{ type:String , default: 'update'  },
    delete_url:{},
})

let document_number =ref( (props.document)? props.document.number: props.new_document_number );

let pervious_document_url = computed(()=>{
  if (props.document ) {
    return route('entry.pervious',{document_catagory:props.document_catagory.name ,document:props.document.number})
  }
  if (props.last_document  ) {
    return route('entry.show',{document_catagory:props.document_catagory.name ,document:props.last_document.number})
  }
  return ""
 })

 let next_document_url = computed(()=>{
  if (props.document ) {
    return route('entry.next',{document_catagory:props.document_catagory.name ,document:props.document.number})
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
// const errors = computed(() => page.props.errors)
const currencies= (page.props.currencies)? page.props.currencies:[];
let document_date = ref( (props.document)? props.document.date : new Date()  )
searchStore.available_currencies.value = currencies

let form_have_been_adjusted = ref(false);

let DeleteModal = ref(false)

let default_account=ref('')

let Etry_Lines = computed(()=>{
  return props.entry_lines.map((line)=>{
    line.customfields = (line.customfields)? JSON.parse(line.customfields ): get_standard_object()
    return{...line }
  })

})


console.log('CreateEntryLines')

let my_lines = [ ...Etry_Lines.value.concat(CreateEntryLines(currencies[0],props.customfields,4))]
let form = ref(my_lines)

const Entry_Totals =computed(()=>{
  let total_of_credit=0
  let total_of_debit=0
  form.value.forEach(line => {
    total_of_debit = !isNaN(Number(line.debit_amount)) && line.debit_amount !=null ? Number(line.debit_amount) + total_of_debit : total_of_debit
    total_of_credit = !isNaN(Number(line.credit_amount)) && line.credit_amount !=null  ? Number(line.credit_amount) + total_of_credit  : total_of_credit
  });
  return { debit_side:total_of_debit , credit_side: total_of_credit  }
});

function clear_form(){
 // rows_count = 30 
  let lines=[]
  for(let index = 0; index < form.value.length ; index++) {
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
  router.delete(route('entry.delete',{document_catagory:props.document_catagory.name ,document:document_number.value}),{
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

  router.put(route('entry.update',{document_catagory:props.document_catagory.name ,document:props.document.number}), data,{
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
  let data={
    document_number:document_number.value ,
    document_catagory_id:props.document_catagory.id,
    entry_lines:form.value ,
    date:DateObject.ToString(document_date.value),
  }
  router.post( route('entry.store',props.document_catagory.id) , data,{
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
      toast.add({ severity: 'success', summary: 'New entry added', detail:'New entry successfully created ' , life: 3000 });
      clear_form()
    },
  })
}
let FirstEmptyLines = computed( ()=>{
  for (let index = 0; index < form.value.length; index++) {
    if ( !  (form.value[index].account || Number(form.value[index].credit_amount) + Number(form.value[index].debit_amount)>0  )   ) {
      return index
    } 
  }
 }) 
 

// add more lines to entries  
function Add_Lines(){
  form.value.push( CreateEntryLines(currencies[0],props.customfields,1)[0])
}



</script>

<template>
  
    <AppLayout title="Dashboard">

      <div class="hidden">
        <div class="mx-auto p-4  text-center" ref="html_to_pdf">hgkjgkjgh
        </div>
      </div>
        <div class=" dark:bg-gray-800  h-screen shadow-xl sm:rounded-lg">
          
          <h1  class="flex  justify-start text-xl w-full bg-sky-700 text-white  px-4  py-2">
            <div class="flex-shrink font-semibold">
              <span v-if="operation=='create'" >New </span> {{ document_catagory.name }}
            </div> 
            <div class="flex-shrink  flex justify-end items-center  mx-4  text-sky-600 ">
              <Link v-if="pervious_document_url" :href="pervious_document_url">
                <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-9 h-9 rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>
              </Link>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="#d1d5db" viewBox="0 0 24 24" stroke-width="1.5" stroke="#d1d5db" class="w-9 h-9 rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>
              <div class=" text-center relative">                
                <input v-model="document_number" id="document_no" form="myform" class=" text-center  rounded-md w-12 h-6 text-gray-700 " >
              </div>
              
              <Link v-if="next_document_url && operation=='update'" :href="next_document_url"  >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-9 h-9 "><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>
              </Link>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="#d1d5db" viewBox="0 0 24 24" stroke-width="1.5" stroke="#d1d5db" class="w-9 h-9 "><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"></path></svg>

            </div>

          </h1>
          <div class="flex flex-col gap-y-2 tab:flex-row dark:text-gray-200 justify-between mx-3 my-4 ">
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
            <div class="flex-initial hidden ">
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
              
              <!-- entry table For mobile  -->
              <div v-if="Device_is_Mobile" class="mx-3 mt-3" >
                
                <div  class="font-semibold flex justify-start  gap-2">
                  <div >Account </div>
                  <div  class=" flex-auto  w-1/2 text-right mr-2"  >Ammount</div> 
                </div>

                <div v-for="(line,index) in form"  :key="index" > 
                    <EntriesTableForMobile  v-model:line="form[index]" :default_line="props.entry_lines[index]"
                    @New_Line_Added="Add_Lines()"  v-slot="slotProps">
                      <div v-show="index==FirstEmptyLines"  @click="slotProps.open_Entry_Modal" class=" font-bold text-lg text-green-600 mt-4 my-3 ">
                        + Add Entry Line
                      </div>
                    </EntriesTableForMobile>
                </div>
               
              </div>

               <!-- entry table For Desktop  -->
                <EntriesTableForDesktop v-if="!Device_is_Mobile"  v-model:entries="form" :customfields="customfields"  />
                <div v-show="!Device_is_Mobile"  @click="Add_Lines" class=" font-bold text-lg text-green-700 mx-3 mt-6 ">
                  + Add Entry Line
                </div> 

                <div class="m-3 tab:w-1/2 lg:w-1/3 grid grid-cols-2 lg:flex     gap-x-2  ">
                  <div class="lg:w-max lg:px-3 text-center rounded-xl font-semibold bg-red-700 text-white">
                     <div>total debit  </div>
                     <div > {{ Entry_Totals.debit_side }}</div>
                  </div>
                  <div class=" lg:px-3 lg:w-max rounded-xl text-center font-semibold  bg-blue-800 text-white">
                     <div>total credit </div>
                     <div>{{ Entry_Totals.credit_side }}</div>
                  </div>
                </div>

               
              <!-- buttons --> 
              <div  class="flex flex-wrap justify-start tab:w-9/12 mobile:my-2  my-0.5 tab:float-right  gap-y-2 ">
                <Link @click.prevent="console.log(form_have_been_adjusted)" href="/create_entry/general_entry/documents" class=" p-2 mx-4 font-semibold rounded-md text-gray-8 border-2 border-gray-500 bg-white "  >
                  <svg class="inline mx-1 h-6 w-6" data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <span>New</span> 
                </Link>

                <button v-if="operation=='update'"  @click="update_document" class=" p-2 mx-2 font-semibold rounded-md bg-white border-2 border-blue-600 text-blue-600 "   >
                  <svg class="inline h-6 w-6 mx-1" data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <span>Update</span>
                </button>
                
                <div @click="DeleteModal=true" class=" p-2 mx-4 font-semibold rounded-md bg-white text-red-700 border-2 border-red-500 " >
                  <svg class="inline h-6 w-6 "  data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <span>Delete</span> 
                </div>
                
                <div @click="download_pdf" class=" p-2 mx-4 font-semibold rounded-md bg-blue-600 text-white  "   >
                  <svg class="inline h-6 w-6 mx-1" data-slot="icon" aria-hidden="true" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <span>Print</span> 
                </div>

                <button  v-if="operation=='create'" class=" p-2 mx-2 font-semibold rounded-md bg-blue-600 text-white "  type="submit" >save</button>

              </div>
            </form>
            <div   class="clear-both"></div>
            <div></div>
            <Toast position="top-left" 
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