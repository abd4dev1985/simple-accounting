<script >
import { ref  } from 'vue'

function get_standard_object(customfields){
  let standard_object ={}
    customfields.forEach(field => {
      standard_object[field]=null
    });
    return standard_object 
}
// create array from the lines of receipt (form)
let Setup_Lines = function(Invoice_Lines,Etry_Lines,Cash_Account,Count_oF_Lines,currency,customfields){
   let Custom_fields = get_standard_object(customfields)
    if ( !Etry_Lines[0]) {
        Etry_Lines[0]={
            debit_amount:null,credit_amount:null,account:Cash_Account,description:null,
            currency:currency,currency_rate:1,cost_center:null , customfields: Custom_fields,
        }
    }

    for (let index = 0; index < Count_oF_Lines; index++) {
        if ( !Invoice_Lines[index]) {
            Invoice_Lines[index]={
                quantity:null,price:null,product:null,ammount:null,description:null,currency:currency,
                currency_rate:1, cost_center:null , customfields: Custom_fields,
            }
        }
        if (!Etry_Lines[index] && index !=0 ) {
            Etry_Lines[index]={
                debit_amount:null,credit_amount:null,account:null,description:null,
                currency:currency,currency_rate:1,cost_center:null , customfields:Custom_fields,
            }  
        }
    }

    let invoice= { Invoice_Lines:Invoice_Lines,Etry_Lines:Etry_Lines};
    return invoice ;
    
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

let Invoice ={
    Invoice_Lines:[] ,
    Etry_Lines:[]  ,
    Setup_Lines:function(Cash_Account,Count_oF_Lines,currency,customfields){
        let Custom_fields = get_standard_object(customfields)
        if ( !this.Etry_Lines[0]) {
            this.Etry_Lines[0]={
                debit_amount:null,credit_amount:null,account:Cash_Account,description:null,
                currency:currency,currency_rate:1,cost_center:null , customfields: Custom_fields,
            }
        }

        for (let index = 0; index < Count_oF_Lines; index++) {
            if ( !this.Invoice_Lines[index]) {
                this.Invoice_Lines[index]={
                    quantity:null,price:null,product:null,ammount:null,description:null,currency:currency,
                    currency_rate:1, cost_center:null , customfields: Custom_fields,
                }
            }
            if (!this.Etry_Lines[index] && index !=0 ) {
                this.Etry_Lines[index]={
                    debit_amount:null,credit_amount:null,account:null,description:null,
                    currency:currency,currency_rate:1,cost_center:null , customfields:Custom_fields,
                }  
            }
        }

    },

    // Add_Lines:Add_Lines,
}




// export {Setup_Lines,Add_Lines};
export  default   Invoice ;


</script>