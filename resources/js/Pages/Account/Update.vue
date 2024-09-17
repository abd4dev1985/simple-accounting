<script setup>

import { reactive ,computed,onUpdated,onMounted,ref,  } from 'vue'
import { usePage,useForm,} from '@inertiajs/vue3';
import searchStore from '../../searchStore.vue';
import AutoComplete from 'primevue/autocomplete';
import DialogModal  from '@/Components/DialogModal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'  
import SecondaryButton from '@/Components/SecondaryButton.vue'  
import ToggleButton from 'primevue/togglebutton';
import { useToast } from "primevue/usetoast";
import Toast from 'primevue/toast';

const ShowUpdateModal = ref(false)
defineExpose({  ShowUpdateModal   })
const page = usePage()
const toast = useToast()

let props =defineProps({
  account:{   } ,
  ParentAccount : {   } ,
})


let  UpdateForm = useForm({
    id:props.account?.id, 
    name: props.account?.name, 
    number: props.account?.number,
    ParentAccount:null,
    has_sons_accounts: true ,
    statment_id:null,
    Currency:page.props.currencies[0]
})

function close_without_save(){
  ShowUpdateModal.value = false
  UpdateForm.reset()
}

function submit(){
    UpdateForm.put(route('accounts.update'),
      {
        onSuccess: () =>{
          ShowUpdateModal.value = false
          UpdateForm.reset()
          toast.add({ severity: 'success', summary: 'Success Message', detail: ' Acoount has been Updated', life: 3000 });

        }
      },
    )
}

onUpdated(() => {
  UpdateForm.id = props.account?.id
  UpdateForm.name = props.account?.name
  UpdateForm.number = props.account?.number
  UpdateForm.ParentAccount = props.ParentAccount
  UpdateForm.has_sons_accounts =  (props.account?.has_sons_accounts)? true:false
  UpdateForm.Currency = page.props.currencies[0]
})
</script>

<template>
  <Toast
      :pt="{ 
        root:{class: 'opacity-95'},
        content: { class:'bg-green-400 text-white' ,},
        icon:{class: 'stroke-white fill-white'},
      }"
    />

 <DialogModal  :show="ShowUpdateModal"  @close="ShowUpdateModal = false" >
    <template #title>
     Update Account 
    </template>

    <template #content>
        <form  @submit.prevent="submit"   id="CreateAccountForm" class="space-y-5" >
          <div>
            <label class="block py-1 text-sm font-semibold" for="">Name</label>
            <div v-if="UpdateForm.errors.name" class="text-red-600">
              {{ UpdateForm.errors.name }}
            </div>
            <input v-model="UpdateForm.name" type="text" class="rounded-lg border border-slate-300">
          </div>
          <div>

            <label class="block py-1 text-sm font-semibold" for="">Number</label>
            <div v-if="UpdateForm.errors.number" class="text-red-600">
              {{ UpdateForm.errors.number }}
            </div>
            <input v-model="UpdateForm.number" type="text"  class="rounded-lg border border-slate-300">
          </div>
          <div>{{  }}</div>
          <div class=" my-3">
              <label class="block text-sm font-semibold text-left" for="">Parent Account</label>
              <div v-if="UpdateForm.errors.ParentAccount" class="text-red-600">
                   {{ UpdateForm.errors.ParentAccount }}
              </div>
              <AutoComplete v-model="UpdateForm.ParentAccount" :suggestions="searchStore.available_accounts.value"
                  @complete="searchStore.search_account" optionLabel="name" forceSelection 
                  :pt="{
                      input: {
                      class: 'bg-white h-8  py-5  dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                      },
                  }">
              </AutoComplete>
          </div>

          <div class="">
            <label class="block text-sm font-semibold text-left py-1" for="">Having Sub Accounts </label>
            <ToggleButton v-model="(UpdateForm.has_sons_accounts)" onLabel="yes " offLabel="no"
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