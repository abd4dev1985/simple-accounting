<script setup>

import { reactive ,computed,onUpdated,onMounted,ref,  } from 'vue'
import { usePage,useForm,} from '@inertiajs/vue3';
import searchStore from '../../searchStore.vue';
import AutoComplete from 'primevue/autocomplete';
import ConfirmationModal  from '@/Components/ConfirmationModal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'  
import SecondaryButton from '@/Components/SecondaryButton.vue'  
import ToggleButton from 'primevue/togglebutton';
import { useToast } from "primevue/usetoast";
import Toast from 'primevue/toast';

const ShowDeleteModal = ref(false)
defineExpose({  ShowDeleteModal   })
const page = usePage()
const toast = useToast()

let severity_style = ref('')

let props =defineProps({
  account:{   } ,
  ParentAccount : {   } ,
})


let  DeleteForm = useForm({
    name: props.account?.name, 
    number: props.account?.number,
    ParentAccount:null,
    has_sons_accounts: true ,
    statment_id:null,
    Currency:page.props.currencies[0]
})

function close_without_save(){
  ShowDeleteModal.value = false
  DeleteForm.reset()
}

function submit(){
    DeleteForm.delete(route('accounts.destroy',props.account.id),
      {
        onSuccess: () =>{
          ShowDeleteModal.value = false
          DeleteForm.reset()
          severity_style.value ='bg-green-400 text-white'
          toast.add({ severity: 'success', summary: 'Success Message', detail: 'Account has been deleted', life: 3000 });
        },
        onError:(errors)=>{
            ShowDeleteModal.value = false
            DeleteForm.reset()
            for (const key in errors) {
                if (Object.hasOwnProperty.call(errors, key)) {
                    severity_style.value ='bg-red-500 text-white'
                    toast.add({ severity: 'error', summary: 'Delete is failed', detail:errors[key] , life: 3000 });
                }
            }

        }
      },
    )
}

onUpdated(() => {
 
  DeleteForm.name = props.account?.name
  DeleteForm.number = props.account?.number
  DeleteForm.ParentAccount = props.ParentAccount
  DeleteForm.has_sons_accounts =  (props.account?.has_sons_accounts)? true:false
  DeleteForm.Currency = page.props.currencies[0]
})
</script>

<template>
  <Toast
      :pt="{ 
        root:{class: 'opacity-95'},
        content: { class:severity_style ,},
        icon:{class: 'stroke-white fill-white'},
      }"
    />

 <ConfirmationModal  :show="ShowDeleteModal"  @close="ShowDeleteModal = false" >
    <template #title >
        <h1 class="font-semibold my-1">
            Delete  Account 
        </h1>   
        <form  @submit.prevent="submit"   class="space-y-5" >
        </form>
    </template>

    <template #content>
        <div class=" my-4">
            <p>
                Do you want to delete <span class="text-red-600 font-semibold">{{ DeleteForm.name }} </span>
                account permanently
            </p> 
            <p class="my-2">
                <span class="font-extrabold mx-3">Important Notice :</span>
                You can not delete account wich have sub account or entries
            </p>
        </div>
       
    </template>

    <template #footer> 
        <div class="flex justify-around w-full">
            <SecondaryButton @click.native="close_without_save">
                 Cancel
            </SecondaryButton>
            <PrimaryButton   class="ml-2 bg-red-600" @click="submit" >
                Delete
            </PrimaryButton>
        </div>
        
    </template>



 </ConfirmationModal>



</template>