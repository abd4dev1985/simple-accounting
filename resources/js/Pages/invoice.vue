<script setup>

import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import Table from '@/Components/Table.vue';
import { reactive } from 'vue'

let props =defineProps({
    entry_lines:{ type: Array , default:[] }   ,
    document_catagory:{   }   ,
    document : {        },
    currencies : { type: Array , default:[]   },
})


</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                HEADER dfd
            </h2>
        </template>

        <div class="">
            <div class="">
                <div class="max-w-7xl dark:bg-gray-800  shadow-xl sm:rounded-lg">
                    <h1  class="text-2xl text-gray-700 w-56 text-right pt-1">New {{ document_catagory.name }}</h1>
                    <div>
                        <Table>

                        </Table>
                       
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>