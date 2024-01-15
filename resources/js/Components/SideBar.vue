<script setup>
import { ref } from 'vue';
import { onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SubMenue from '@/Components/SubMenue.vue';

const emit = defineEmits(['Open_Account_LedgerBook','Open_Inventory_Ledger','Open_Costcenter_Ledger'])

defineProps({
    title: String,
});



</script>

<template>
    <div class="  larg:my-1 my-14 py-2">
        <SubMenue   class="dark:text-rose-400 " >
            <template #svg  >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                </svg>
            </template>
            <template #title>
                    Entries 
            </template> 

            <template  #menue_items >
                <div class="py-2 text-left pl-3 "  >  <Link  href="/create_entry/general_entry/documents" >General Entry</Link>  </div>
                <div  class="py-2 text-left pl-3 " >Begin Entry </div>       
            </template> 
        </SubMenue>
        <SubMenue   class="dark:text-rose-400 " >
            <template #svg  >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                </svg>
            </template>
            <template #title>
                    Accounts 
            </template> 

            <template  #menue_items >
                <div  @click="$emit('Open_Account_LedgerBook')" class="py-1 text-left pl-3 cursor-pointer "  >Ledger Book</div>
                <div  class="py-1 text-left pl-3 cursor-pointer  " >Begin Entry </div>       
            </template> 
        </SubMenue>
        <SubMenue   class="dark:text-rose-400 " >
            <template #svg  >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                </svg>
            </template>
            <template #title>
                    Cost center 
            </template> 

            <template  #menue_items >
                <div  @click="$emit('Open_Costcenter_Ledger')" class="py-1 text-left pl-3 cursor-pointer "  >Ledger Book</div>
                <div  class="py-1 text-left pl-3 cursor-pointer  " >Cost Center Ledger  </div>       
            </template> 
        </SubMenue>
        


        <SubMenue   class="dark:text-rose-400 " >
            <template #svg  >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                </svg>
            </template>
            <template #title>
                    inventories 
            </template> 

            <template  #menue_items >
                <div @click="$emit('Open_Inventory_Ledger')" class="py-1 text-left pl-3 cursor-pointer "  >Inventory Ledger</div>
                <div  class="py-1 text-left pl-3 " >Begin Entry </div>       
            </template> 
        </SubMenue>

        <SubMenue   class="dark:text-rose-400 " >
            <template #svg  >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                </svg>
            </template>
            <template #title>
                    Accounts 
            </template> 

            <template  #menue_items >
                <div  @click="$emit('Open_Account_LedgerBook')" class="py-1 text-left pl-3 cursor-pointer "  >Ledger Book</div>
                <div  class="py-1 text-left pl-3 cursor-pointer  " >Begin Entry </div>       
            </template> 
        </SubMenue>
        <SubMenue   class="dark:text-rose-400 " >
            <template #svg  >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                </svg>
            </template>
            <template #title>
                Purchases
            </template> 

            <template  #menue_items >            
                <div  class="py-1 text-left pl-3 " >
                    <Link  href="/create_purchase/purchase_invoice/documents" >General Purchases</Link>
                </div>       
            </template> 
        </SubMenue>
    </div>
</template>