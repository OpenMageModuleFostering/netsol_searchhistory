
Validation.add('validate-number-of-searchterm','No. of search term value should be less than maximum product count value',function(the_field_value) {
	var pa_searchsetting_pa_searchhistory_search_max_product_count = document.getElementById("pa_searchsetting_pa_searchhistory_search_max_product_count").value;
	if(parseInt(the_field_value) < parseInt(pa_searchsetting_pa_searchhistory_search_max_product_count))
	{
		return true;
	}
	return false;
});

Validation.add('validate-max-search-products','Maximum product count value should be greater than No. of search term value',function(the_field_value) { 
	var pa_searchsetting_pa_searchhistory_search_term_count = document.getElementById("pa_searchsetting_pa_searchhistory_search_term_count").value;
	if(parseInt(the_field_value) > parseInt(pa_searchsetting_pa_searchhistory_search_term_count))
	{
		return true;
	}
	return false;
});
