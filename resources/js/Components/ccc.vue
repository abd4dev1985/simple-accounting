<script setup>
import { onMounted, ref ,computed,onUpdated ,reactive} from 'vue';
import AutoComplete from 'primevue/autocomplete';
import searchStore from '../searchStore.vue';
import { Link,  } from '@inertiajs/vue3';

let props=  defineProps(['modelValue','TableObject','rows_index','columns_index',
'Format','SearchFunction','Suggestions','Required','Default','ReadOnly', 'Invalid' ]);

const emit = defineEmits(['update:modelValue','change','UpdateCurrencyRate',])
let Red_Alerte=ref(false)
const value = computed({
  get() {
    if (props.Format =='number' ) {
      //console.log(Number(props.modelValue))
      return  ( isNaN(Number(props.modelValue)) || Number(props.modelValue)==0  ) ? props.Default:props.modelValue  
      //return  ( isNaN(Number(props.modelValue)) && Number(props.modelValue)!=null) ? props.Default:props.modelValue  

    }else {
      return props.modelValue ?? props.Default 
   }
  },
  set(value) {
    emit('update:modelValue', value)
    emit('change',value)
    // emit UpdateCurrencyRate event
    if (typeof value ==='object' && value !=null  ) {
      if ('default_rate' in value ) {emit('UpdateCurrencyRate',value.default_rate)  }
    }
    // emit UpdateCurrencyRate event
    if (props.Currency && value==null  ) {
      emit('UpdateCurrencyRate',14000)
    }

    if (props.Required && value=='' ) {
      Red_Alerte.value=true
    }else{Red_Alerte.value=false}
    

  }
})
let mytable 


let keyboared_Navigation = ref(true)


const formatter =Intl.NumberFormat('en')

onUpdated(()=>{
 
 mytable = computed(()=>  props.TableObject  ) 
 // console.log('update input')
 // console.log(mytable.value)
})

function force_number(event){
 if ( isNaN( Number(event.target.value) )    ) {
  event.target.value=null ;
  }
}

function format_number( value ){
  if (  !isNaN(Number(value))  &&  Number(value) != 0    ) {
    return formatter.format(value)
  }else{  
    return null 
  }

}

function focus_input(event){
  event.target.previousElementSibling.focus()
  //console.log(event.target.previousElementSibling.value)
}

function focusDown(Rows,row_index,collumn_index)
{
  let rows_count = mytable.value.Rows.length
  if ( keyboared_Navigation.value && row_index<rows_count -1 ) {
      Rows[row_index+1].children[collumn_index].children[0].children[0].focus()
  } 
}

function focusUp(Rows,row_index,collumn_index){

    let scrollable_table =mytable.value.Table
    let table_header_postion =  mytable.value.TableHeader.getBoundingClientRect().bottom 

    if (keyboared_Navigation.value && row_index>0) {
        Rows[row_index-1].children[collumn_index].children[0].children[0].focus()
    }

    let table_row_postion = Rows[row_index].getBoundingClientRect().bottom
    
    if (table_row_postion-table_header_postion <= 60 ) {
        if (keyboared_Navigation.value && row_index>1) {
            Rows[row_index-2].children[collumn_index].children[0].children[0].focus()
        }
        focusDown(Rows,row_index-1,collumn_index)
    }

    if (row_index==1) {
    scrollable_table.scroll(scrollable_table.scrollLeft ,-25)
    }
}

function focusRight(Rows,row_index,collumn_index){
  let Cell =Rows[row_index]?.children[collumn_index+1]?.children[0]
  if (keyboared_Navigation.value && Cell ) {
    //console.log(Cell.children[0])
    Cell.children[0].focus()
  }
}  

function focusLeft(Rows,row_index,collumn_index){
    if (keyboared_Navigation.value && collumn_index>1) {
        Rows[row_index].children[collumn_index-1].children[0].children[0].focus()
  }
}  


</script>
<template>
    <div v-if="props.Format=='number'"  class="relative group">
        <input v-model="value"   :class="{ 'border-red-500 border-2 focus:border-none': Invalid}"
        @keydown.up.prevent="focusUp(TableObject.Rows,rows_index,columns_index)"
        @keydown.down.prevent="focusDown(TableObject.Rows,rows_index,columns_index)"
        @keyup.left="focusLeft(TableObject.Rows,rows_index,columns_index)"
        @keyup.right="focusRight(TableObject.Rows,rows_index,columns_index)"
        @focus="force_number" :readonly="ReadOnly"
        class="bg-inherit text-transparent focus:text-gray-950 dark:focus:text-gray-200 py-3 mobile:w-20 mobile:text-sm w-28 max-w-max text-center rounded-md ring-offset-1 focus:ring-2 "> 
        <div v-if="Red_Alerte"  @click="focus_input"  class="block absolute group-focus-within:hidden   h-1/3 mobile:text-sm
         text-center w-full   top-1/2 left-1/2  -translate-x-1/2 -translate-y-1/2 text-base text-red-600 ">
             Required
        </div> 
        <div v-else  @click="focus_input"  class="block absolute  group-focus-within:hidden   h-1/3 mobile:text-sm text-center w-full   top-1/2 left-1/2  -translate-x-1/2 -translate-y-1/2 ">
            {{ format_number(value) }}
        </div>  

    </div>

    <AutoComplete  v-if="Format=='aoutcomplete'" v-model="value" :suggestions="Suggestions"
    :class="{'p-invalid':Invalid}" optionLabel="name" forceSelection 
    @complete="SearchFunction" @change="$emit('change')"   
    @show="keyboared_Navigation=false"   @hide="keyboared_Navigation=true"
    @keyup.up="focusUp(TableObject.Rows,rows_index,columns_index)"    @keyup.down="focusDown(TableObject.Rows,rows_index,columns_index)"
    @keyup.left="focusLeft(TableObject.Rows,rows_index,columns_index)"   @keyup.right="focusRight(TableObject.Rows,rows_index,columns_index)" 
    :pt="{
      root:{class:Invalid? 'border-red-500 border-2 focus:border-none':null   ,   
      },
      input: { class: 'bg-inherit py-2 h-full  dark:text-gray-200 text-inherit  ring-offset-2  focus:ring-2 border-none ' },
    }">
      <template #empty>
        <div   class="font-semibold p-3 border-2 border-blue-500">
            <slot name="emptySuggestions"></slot>
        </div>
      </template> 
    </AutoComplete> 

    <div v-if="props.Format=='text'"  >
      <input v-model="value" @keyup.up="focusUp(TableObject.Rows,rows_index,columns_index)"
      @keydown.down.prevent="focusDown(TableObject.Rows,rows_index,columns_index)"
      @keyup.left="focusLeft(TableObject.Rows,rows_index,columns_index)"
      @keyup.right="focusRight(TableObject.Rows,rows_index,columns_index)"
      class="bg-inherit  w-full py-3 rounded-md ring-offset-1 focus:ring-2 ">
    </div>

   



    
</template>