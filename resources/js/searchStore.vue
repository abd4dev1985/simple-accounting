<script  >

import { Head, Link, router,usePage, } from '@inertiajs/vue3';
import { ref, computed,reactive,onMounted, } from 'vue';
import axios from 'axios' ;

const page = usePage();

const available_accounts = ref([]);
const available_products= ref([]);
const available_cost_centers = ref([]);
const available_custom_fields = ref([]);

let selected_account = ref();
const available_currencies = ref([]);
const filterd_currencies = ref([]);


let create_new_account_link=ref('');
let create_new_currencey_link=ref('');
let create_new_costcenter_link=ref('');
let create_new_CustomField_link=ref('');

let create_new_product_link = ref('');

const search_account =(event )=>{
    axios.post('/search/account',{searchForAccount:event.query})
    .then(function (response) {
        //console.log(response.data )
        available_accounts.value=response.data  
        if (available_accounts.value.length==0) {
            create_new_account_link.value = '/accounts/create?new_account_name='+event.query
        }
    })
    .catch(function (error) {
        console.log(error);
    });

}
const search_product =(event)=>{
    console.log(event.query)
    axios.post('/search/product',{searchForProduct:event.query})
    .then(function (response) {
        available_products.value=response.data  
        if (available_products.value.length==0) {
            create_new_product_link.value = '/products/create?new_account_name='+event.query
        }
    })
    .catch(function (error) {
        console.log(error);
    });

}



const search_currencey =(event)=>{
    setTimeout(() => {
        if (!event.query.trim().length) {
            filterd_currencies.value = available_currencies.value
        }
        filterd_currencies.value = available_currencies.value.filter((currencey) => {
            return currencey.name.toLowerCase().startsWith(event.query.toLowerCase());
         });
         if (filterd_currencies.value.length==0) {
            create_new_currencey_link.value = '/currencies/create?new_currencey_name='+event.query
        }
        
    }, 200);

}
const search_cost_center =(event)=>{
    axios.post('/search/cost_center',{searchForCostCenter:event.query})
    .then(function (response) {
        available_cost_centers.value=response.data  
        if (available_cost_centers.value.length==0) {
            create_new_costcenter_link.value = '/cost_centers/create?new_costcenter_name='+event.query
        }
    })
    .catch(function (error) {
        console.log(error);
    });

}
// available_custom_fields
const search_custom_fieled =(event)=>{
    axios.post('/search/CustomeField',{searchForCustomfield:event.query})
    .then(function (response) {
        available_custom_fields.value=response.data  
        if (available_custom_fields.value.length==0) {
            create_new_CustomField_link.value = '/CustomeFields/create?new_CustomeField_name='+event.query
        }
    })
    .catch(function (error) {
        console.log(error);
    });

}



const reset_search=()=>{
    available_accounts.value=[]
}

const search__account = (event) => {

router.reload({
    only: ['suggested_accounts'],
    data:{searchForAccount:event.query} ,
    onSuccess:(page)=>{
        available_accounts.value=page.props.suggested_accounts 
        if (available_accounts.value==0) {
            create_new_account_link.value = '/accounts/create?new_account_name='+event.query
        } 
    },
    onError:(errors)=>{
        available_accounts.value=page.props.suggested_accounts
        if (available_accounts.value==0) {
            create_new_account_link.value = '/accounts/create?new_account_name='+event.query
        } 
    },
    
})

}

let searchStore ={

    available_accounts,
    search__account,
    search_account,
    create_new_account_link,

    selected_account ,

    available_products,
    search_product,
    create_new_product_link,

    available_custom_fields,
    search_custom_fieled,
    create_new_CustomField_link,
    
    reset_search,

    available_currencies,
    filterd_currencies,
    search_currencey,
    create_new_currencey_link ,

    available_cost_centers,
    search_cost_center,
    create_new_costcenter_link,
} 
export  default   searchStore ;
    
</script>