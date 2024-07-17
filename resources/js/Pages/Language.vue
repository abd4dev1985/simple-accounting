<script  >

import { Head, Link, router,usePage, } from '@inertiajs/vue3';
import { ref, computed,reactive,onMounted, } from 'vue';
import axios from 'axios' ;


const CurrentLanguage = ref('en');

let Translate = function(key){
    if (!Local[CurrentLanguage.value][key]) {
        return key
    }
    return Local[CurrentLanguage.value][key]
}
   
let Local ={
    en:{
        Account: 'Account' ,
        Credite: 'Credite' ,
        Debite : 'Debite',
        Description : 'Description' ,
        CostCenter : 'Cost Center' ,
        Currency : 'Currency' ,
        rate : 'rate' ,
        Entry :'Entry',
        Invoices: 'Invoices' , 
        FinancialStatments: 'Financial Statments',
    },
    ar:{
        Account: 'الحساب' ,
        Credite: 'دائن' ,
        Debite : 'مدين',
        Description : 'البيان' ,
        CostCenter : 'كركز الكلفة' ,
        Currency : 'العملة' ,
        rate : 'العملة' ,
        Entry :'قيد',
        Invoices: 'فواتير' , 
        FinancialStatments: 'قوائم مالية',
    }



}



let Language ={ CurrentLanguage ,Translate  } 
export  default   Language ;
    
</script>