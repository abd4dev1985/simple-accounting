<script setup>

import { reactive ,computed,watch,onMounted,ref,  } from 'vue'
import { usePage,useForm,} from '@inertiajs/vue3';
import searchStore from '../../searchStore.vue';
import AutoComplete from 'primevue/autocomplete';
import DialogModal  from '@/Components/DialogModal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'  
import SecondaryButton from '@/Components/SecondaryButton.vue'  
import ToggleButton from 'primevue/togglebutton';
import { useToast } from "primevue/usetoast";
import Toast from 'primevue/toast';


const ShowCreateModal = ref(false)
defineExpose({  ShowCreateModal   })
const page = usePage()
const toast = useToast()

const   CreateForm = useForm({
  name: null, 
  number:null,
  ParentAccount:null,
  has_sons_accounts: false ,
  statment_id:null,
  Currency:page.props.currencies[0]
})

function close_without_save(){

  ShowCreateModal.value = false
  CreateForm.reset()
}


function submit(){
    CreateForm.post(route('accounts.store'),
      {
        onSuccess: () =>{
          ShowCreateModal.value = false
          CreateForm.reset()
          toast.add({ severity: 'success', summary: 'Success Message', detail: 'New Acoount Added', life: 3000 });
        }
      },
    )
}

</script>

<template>

  <Toast
    :pt="{ 
      root:{class: 'opacity-95'},
      content: { class:'bg-green-400 text-white' ,},
      icon:{class: 'stroke-white fill-white'},
    }"
  />

 <DialogModal  :show="ShowCreateModal"  @close="ShowCreateModal = false" >
    <template #title>
      <h1 class="text-2xl  font-semibold mb-5">
        Create New Account
      </h1>
    </template>

    <template #content>
        <form  @submit.prevent="submit"   id="CreateAccountForm" class="space-y-6 " >

          <div class="w-full">
            <label class="block py-1 text-sm font-semibold text-black" for="">Name</label>
            <div v-if="CreateForm.errors.name" class="text-red-600">
              {{ CreateForm.errors.name }}
            </div>
            <input v-model="CreateForm.name" type="text" class="w-full py-3 rounded-lg focus:ring-2 border border-slate-300">
          </div>

          <div class="w-full">
            <label class="block py-1 text-sm font-semibold text-black" for="">Number</label>
            <div v-if="CreateForm.errors.number" class="text-red-600">
              {{ CreateForm.errors.number }}
            </div>
            <input v-model="CreateForm.number" type="text" class="w-full py-3 rounded-lg focus:ring-2 border border-slate-300">
          </div>

          <div class="w-full">
              <label class="block py-1 text-sm font-semibold  text-black" for="">Parent Account</label>
              <div v-if="CreateForm.errors.ParentAccount" class="text-red-600">
                   {{ CreateForm.errors.ParentAccount }}
              </div>
              <AutoComplete v-model="CreateForm.ParentAccount" :suggestions="searchStore.available_accounts.value"
                  @complete="searchStore.search_account" optionLabel="name" forceSelection 
                  :pt="{
                      root:{
                        class: 'w-full bg-white',
                      },
                      input: {
                        class: 'h-8  w-full py-6  rounded-lg dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                      },
                  }">
              </AutoComplete>
          </div>

          <div class="">
            <label class="block text-sm font-semibold text-left py-1" for="">Having Sub Accounts </label>
            <ToggleButton v-model="CreateForm.has_sons_accounts" onLabel="yes " offLabel="no"
            :pt="{
              root: {
              class: ' h-8 focus:ring-2',
              }, 
            }"/>
          </div>



.
        
        </form>
    </template>

    <template #footer> 
        <div class="flex justify-around w-full">
            <SecondaryButton @click.native="close_without_save">
            Cancel
            </SecondaryButton>
            <PrimaryButton   class="ml-2" @click="submit" >
                Save & Close
            </PrimaryButton>
        </div>
        
    </template>



 </DialogModal>



</template>