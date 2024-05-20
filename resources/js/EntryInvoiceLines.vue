<script >
import { ref  } from 'vue'

function get_standard_object(customfields){
  let standard_object ={}
    customfields.forEach(field => {
      standard_object[field]=null
    });
    return standard_object 
}
let Create_Invoice_Line = function(number,Currency,customfields){
    let Custom_fields = get_standard_object(customfields)
    let Invoice_Lines=[]
    for (let index = 0; index < number; index++) {
        Invoice_Lines[index]={
            quantity:null,price:null,product:null,ammount:null,description:null,currency:Currency,
            currency_rate:1, cost_center:null , customfields: Custom_fields,
        }
    }
    return Invoice_Lines
}

let Create_Entry_Line = function(Account,number,Currency,customfields){
    let Custom_fields = get_standard_object(customfields)
    let Entry_Lines=[]
    for (let index = 0; index < number; index++) {
        Entry_Lines[index]={
            debit_amount:null,credit_amount:null,account:Account,description:null,
            currency:Currency,currency_rate:1,cost_center:null , customfields: Custom_fields,
        }
    }
    return Entry_Lines
}




let Add_Lines = function(Invoice_Lines,Etry_Lines,number,currency,customfields){
    let Custom_fields = get_standard_object(customfields)
    let invoice_Line ={ quantity:null,price:null,product:null,ammount:null,description:null,currency:currency,currency_rate:1, cost_center:null , customfields: Custom_fields,}
    let entry_Line ={debit_amount:null,credit_amount:null,account:null,description:null,currency:currency,currency_rate:1,cost_center:null ,customfields:Custom_fields,}

    for (let index = 0; index < number; index++) {
        Invoice_Lines.push(invoice_Line)
        Etry_Lines.push(entry_Line)
    }

} 






export {Create_Invoice_Line , Create_Entry_Line};


</script>