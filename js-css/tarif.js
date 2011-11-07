function clearData() {
	jQuery("#base_location_code").val(''); 
}

function formatItem(row) {
	return row[0];;
}

function findBaseLocationValue(event,data) { 
	jQuery("#base_location").val(data[0]); 
	jQuery("#base_location_code").val(data[1]);
		
	if(jQuery.trim(data[1])=='null')
	{
		jQuery("#base_location").val(''); 
		jQuery("#base_location_code").val('');
	} 
};

function findValueDestination(event,data) { 
	jQuery("#dest_location").val(data[0]); 
	jQuery("#dest_location_code").val(data[1]);
		
	if(jQuery.trim(data[1])=='null')
	{
		jQuery("#dest_location").val(''); 
		jQuery("#dest_location_code").val('');
	} 
	
	jQuery("*[class^='ishipp']").remove();
   jQuery('.wpsc_shipping_header').after('<tr class="load-tarif"><td colspan="5">Loading...</td></tr>');
	
	jQuery.post(ishipp.ajaxurl, {action: 'GETTARIF', to: data[0], destination_code: data[1]}, function(data) {
      jQuery("*[class^='ishipp']").remove();
      jQuery("*[class^='load-tarif']").remove();
      jQuery('.wpsc_shipping_header').after(data);
	});
};

function destLocationForm() {
   jQuery.get(ishipp.ajaxurl, {action: 'DESTLOCFORM'}, function(data) {
      jQuery('.wpsc_change_country').after(data);

      //destination
      jQuery("#dest_location").autocomplete(ishipp.ajaxurl + "?action=GETCITY",{width:250,minChars:3, matchSubset:1, matchContains:1, max:10, cacheLength:20, formatItem:formatItem, selectOnly:1, autoFill:false, cleanUrl:false, multiple:true, multipleSeparator:'|', scroll:false});	
      jQuery("#dest_location").result(findValueDestination).next().click(function(){	});
   }); //alert(cnt.responseText);
   //jQuery('.wpsc_change_country').after(cnt.responseText);
 /*  jQuery('.wpsc_change_country').after('<tr class="change_dest_location"><td colspan="5">'
      + 'City: <input type="text" name="dest_location" id="dest_location" value="" />'
      + '<input type="hidden" id="dest_location_code" name="dest_location_code" value="" /></td></tr>');
*/
}

function baseLocationForm() {
	//base location
	jQuery("#base_location").autocomplete(ishipp.ajaxurl + "?action=GETCITY&ind=0",{minChars:3, matchSubset:1, matchContains:1, max:10, cacheLength:20, formatItem:formatItem, selectOnly:1, autoFill:false, cleanUrl:false, multiple:true, multipleSeparator:'|', scroll:false});	
	jQuery("#base_location").result(findBaseLocationValue).next().click(function(){	});
}


jQuery(document).ready(function(){

	//base location
	jQuery("#base_location").autocomplete(ishipp.ajaxurl + "?action=GETCITY&ind=0",{minChars:3, matchSubset:1, matchContains:1, max:10, cacheLength:20, formatItem:formatItem, selectOnly:1, autoFill:false, cleanUrl:false, multiple:true, multipleSeparator:'|', scroll:false});	
	jQuery("#base_location").result(findBaseLocationValue).next().click(function(){	});
	
	//destination
   destLocationForm();
   
/*	jQuery("#dest_location").autocomplete(ishipp.ajaxurl + "?action=GETCITY",{width:200,minChars:3, matchSubset:1, matchContains:1, max:10, cacheLength:20, formatItem:formatItem, selectOnly:1, autoFill:false, cleanUrl:false, multiple:true, multipleSeparator:'|', scroll:false});	
	jQuery("#dest_location").result(findValueDestination).next().click(function(){	});

	jQuery("#clearForm").click(function(){ clearData();});
	
	//allowed character
	jQuery("#base_location").alpha({allow:", "});
	jQuery("#destination").alpha({allow:", "});
	jQuery("#weight").numeric({allow:".,"});

	
	jQuery("#checktariff").click(function(){
		if(jQuery("#weight").val()=='')jQuery("#weight").val(1);
		return tariffValidate();			
	});
			*/
});
