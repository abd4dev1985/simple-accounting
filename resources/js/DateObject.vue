<script  >

import { Head, Link, router,usePage, } from '@inertiajs/vue3';
import { ref, computed,reactive,onMounted, } from 'vue';

//convert date format from local to utc 
function Local_To_UTC(date){
  let Minutes_Diffrencs =date.getTimezoneOffset()
  if (Minutes_Diffrencs<0) {
    date.setMinutes(date.getMinutes() - Minutes_Diffrencs);
  }else{  
    date.setMinutes(date.getMinutes() + Minutes_Diffrencs);
  }
  return date
}

function  ToString (date){
  if ( typeof date == 'string') {
    return date
  }else{ 
    return Local_To_UTC(date).toISOString().slice(0,10) }
}


let DateObject ={  ToString , Local_To_UTC } 

export  default   DateObject ;
    
</script>