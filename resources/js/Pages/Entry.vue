<script setup>

import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive ,computed,watch,onUpdated,ref,onMounted  } from 'vue'
import { Head, Link, router,usePage,useRemember,useForm} from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import "primevue/resources/themes/lara-light-indigo/theme.css";
import searchStore from '../searchStore.vue';
import DateObject from '../DateObject.vue';
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
import { useWinBox } from 'vue-winbox'

const toast = useToast();
let screenWidth=ref(0);
screenWidth.value= document.getElementById("app").offsetWidth
let Device_is_Mobile = ref(screenWidth.value <=800?true:false )

onMounted(() => {
    if (window !== undefined) {
        window.addEventListener('resize', ()=>{
        screenWidth.value= document.getElementById("app").offsetWidth
        Device_is_Mobile.value = screenWidth.value <=800?true:false 

        })
    }
})

let severity_style= ref('');
let Translate = Language.Translate

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
let document_number =ref( (props.document)? props.document.number: props.new_document_number );
let document_date = ref( (props.document)? props.document.date : new Date()  )
searchStore.available_currencies.value = currencies

let form_have_been_adjusted = ref(false);

let DeleteModal = ref(false)

let default_account=ref('')

let Etry_Lines = computed(()=>{
  return props.entry_lines.map((line)=>{
    line.customfields = (line.customfields)? JSON.parse(line.customfields ): get_standard_object()
    return line 
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


</script>

<template>
    <AppLayout title="Dashboard">
        <div class=" dark:bg-gray-800  h-screen shadow-xl sm:rounded-lg">
          
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
              <div v-if="false" class="mx-3 mt-7"  >
                <div v-for="(line,index) in form"  :key="index" > 
                    <EntriesTableForMobile  v-model:line="form[index]" @change="get_ammount(index)" :default_line="props.entry_lines[index]"  >
                    </EntriesTableForMobile>
                </div>
                <EntriesTableForMobile  v-model:line="form[form.length-1]" @New_Line_Added="Add_Lines()"  v-slot="slotProps">
                  <div @click="slotProps.open_Entry_Modal" class="font-semibold text-green-600 mt-4 ">  + Add Line</div>
                </EntriesTableForMobile>
              </div>
               <!-- entry table For Desktop  -->
               <EntriesTableForDesktop v-show="true"  v-model:entries="form" :customfields="customfields"  />

                <div class="mx-5 w-48  ">Total</div>
                <div class=" w-60 inline-flex justify-around bg-sky-200  ">
                  <ccc v-model="Entry_Totals.debit_side"  :disabled="true"  Format="number" />
                  <ccc v-model="Entry_Totals.credit_side" :disabled="true"   Format="number" />
                </div>
              <!-- buttons --> 
              <div  class="flex justify-start tab:w-9/12 mobile:my-2  my-0.5 tab:float-right   ">
                <Link @click.prevent="console.log(form_have_been_adjusted)" href="/create_entry/general_entry/documents" class=" p-2 mx-4 font-semibold rounded-md bg-black text-white "  >New </Link>
                <button v-if="operation=='update'"  @click="update_document" class=" p-2 mx-2 font-semibold rounded-md bg-blue-600 text-white "   >Update</button>
                <div @click="DeleteModal=true" class=" p-2 mx-4 font-semibold rounded-md bg-red-600 text-white "   >Delete</div>
                <button class=" p-2 mx-4 font-semibold rounded-md bg-blue-600 text-white "   >Print</button>
                <button  v-if="operation=='create'" class=" p-2 mx-2 font-semibold rounded-md bg-blue-600 text-white "  type="submit" >save</button>

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