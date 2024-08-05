<script  >

function get_standard_object( custom_fields_array){
    let standard_object ={}
    custom_fields_array.forEach(field => {
        standard_object[field]=null
    });
    return standard_object 
}

function CreateEntryLines( currency ,custom_fields,number=1  ) {
  let array =[]
  let line = {
    debit_amount:null,  credit_amount:null, account:null, description:null,
    currency:currency, currency_rate:1, cost_center:null , customfields: get_standard_object(custom_fields)
  }

  for (let index = 0; index < number; index++) {
    array.push({... line})
  }

  return array

}



export  default   CreateEntryLines ;

</script>