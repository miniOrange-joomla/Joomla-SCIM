var countUserProfileAttributesRows;
var countUserFieldAttributesRows;
var db;
window.addEventListener('DOMContentLoaded', function(){
        countUserProfileAttributesRows = document.getElementsByClassName("userProfileAttributeRows").length;
        countUserFieldAttributesRows   = document.getElementsByClassName("userFieldAttributeRows").length;
        let userProfilePlusButton = document.getElementById("moScimUserProfilePlusButton");
        if(userProfilePlusButton !=null){
            userProfilePlusButton.addEventListener('click',function () {
                add_user_attibute(this,"profile",countUserProfileAttributesRows);
                countUserProfileAttributesRows++;
            });
        }


        let attributeMinusButtons = document.getElementsByClassName("userAttributeMinusButton");
        if(attributeMinusButtons.length>0){
            for (let i=0;i<attributeMinusButtons.length;i++)
                attributeMinusButtons[i].addEventListener('click',function () {
                    remove_user_attibute(this);
                });
        }


    }

);


function add_user_attibute(plusButton,type,countUserAttributes){
    var scimFiledOptions = getOptions('moScimUserFieldsFromDb');
    var allJoomlaAttributeOptions = getOptions('moScimAllJoomlaAttributes');
    // Find the last attribute row instead of the last child (which could be the save button)
    var containerDiv = document.getElementById("user"+type[0].toUpperCase()+type.slice(1)+"AttrDiv");
    var attributeRowsClass = type === "profile" ? "userProfileAttributeRows" : "userFieldAttributeRows";
    var attributeRows = containerDiv.getElementsByClassName(attributeRowsClass);
    var lastRow = attributeRows.length > 0 ? attributeRows[attributeRows.length - 1] : null;
    var sel = "<div class='row userAttr userFieldAttributeRows' style='padding-bottom:1%;' id='uparow_"+type+"_" + countUserAttributes + "'><select type='text' class='moScimAttributeName mo_scim_textfield' name='user_field_attr_name[" + countUserAttributes + "]'>"+scimFiledOptions+"</select><input type='text' class='moScimAttributeValue mo_scim_textfield' name='user_field_attr_value[" + countUserAttributes + "]' placeholder='SCIM Client Attribute Name' value='' /><input type=\"button\" class=\"mo_boot_btn mo_boot_btn-danger userAttributeMinusButton\" name=\"user_field_attr_minus_button[" + countUserAttributes + "]\" style=\"margin:0px 0px 8px 16px;\" value=\"-\" onclick=\"remove_user_attibute(this)\"/></div>";
    if(type==="profile"){
        var sel = "<div class='mo_boot_row userAttr userProfileAttributeRows' style='padding-bottom:1%;' id='uparow_"+type+"_" + countUserAttributes + "'><select class='moScimAttributeValue mo_scim_textfield'  type='text' name='user_profile_attr_value[" + countUserAttributes + "]'>"+allJoomlaAttributeOptions+"</select><select type='text'  class='moScimAttributeName mo_scim_textfield' name='user_profile_attr_name[" + countUserAttributes + "]'>"+scimFiledOptions+"</select><input type=\"button\" class=\"mo_boot_btn mo_boot_btn-danger userAttributeMinusButton\" name=\"user_profile_attr_minus_button[" + countUserAttributes + "]\" style=\"margin:0px 0px 8px 12px;\" value=\"-\" onclick=\"remove_user_attibute(this)\"/></div>";
    }
    if(lastRow!=null){
        jQuery(sel).insertAfter(lastRow);
    }
    else{
        // If no attributes exist, insert after the column headers div
        var columnHeaders = containerDiv.getElementsByClassName("moScimAttributeColumns");
        if(columnHeaders.length > 0){
            jQuery(sel).insertAfter(jQuery(columnHeaders[0]));
        }
        else{
            // Fallback: insert after the plus button if column headers not found
            jQuery(sel).insertAfter(jQuery("#moScimUser"+type[0].toUpperCase()+type.slice(1)+"PlusButton"));
        }
    }
}

function getOptions(id) {
    var dbField = document.getElementById(id).value;

    dbField = JSON.parse(dbField);
    var userFieldsOptions = "";
    console.log(typeof(dbField))
   if(id==="moScimAllJoomlaAttributes"){
       var type="user";
       userFieldsOptions = userFieldsOptions + '<optgroup label="User Table Fields">';
       for(const property in dbField){

           if(type==="user" && property.slice(0,3)==="up_"){
               type="userField";
               userFieldsOptions = userFieldsOptions + '</optgroup><optgroup label="User Profile Fields">';
           }
           else if(type==="userField" && property.slice(0,3)!=="up_"){
               userFieldsOptions = userFieldsOptions + '</optgroup><optgroup label="User Field Attributes">';
           }
           userFieldsOptions = userFieldsOptions + "<option value='"+property+"'>"+dbField[property]+"</option>";
       }
       userFieldsOptions = userFieldsOptions + '</optgroup>';
   }
   else{
       for(var i=0;i<dbField.length;i++){
           userFieldsOptions = userFieldsOptions + "<option value='"+dbField[i]+"'>"+dbField[i]+"</option>";
       }

   }

    return userFieldsOptions;
}
function myFunction(item, index) {
    document.getElementById("demo").innerHTML += index + ":" + item + "<br>";
}

function remove_user_attibute(minusButton){

    minusButton.parentElement.remove();

}


document.addEventListener('DOMContentLoaded', function () {
    var test = document.querySelectorAll('.mo_scim_faq_page');
    test.forEach(function (header) {
        header.addEventListener('click', function () {
            var body = this.nextElementSibling;
            body.style.display = body.style.display === 'none' || body.style.display == "" ? 'block' : 'none';
        });
    });
});

jQuery(document).ready(function() {
    jQuery('#idpguide').on('change', function () {
        var selectedIdp = jQuery(this).find('option:selected').val();
        window.open(selectedIdp, '_blank');
    });
});
