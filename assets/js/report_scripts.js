jQuery(document).ready(function(){

	jQuery(".start_date, .end_date, .recieved_date").datepicker({
		"dateFormat" : "YY-mm-dd"
	});
	
	jQuery("button.button_dispaly_modal").click(function(){
		jQuery("#entery_id").val(0);
		jQuery("#myModal").fadeIn();
		jQuery("input.edit_filed").removeAttr('readonly').val("");
	});
	
	jQuery(document).on('click','a.button_dispaly_modal', function(event) {
		var id = jQuery(this).data("id");
		jQuery("#entery_id").val(id);
		jQuery("#myModal").fadeIn();		
		jQuery("input.edit_filed").attr('readonly','readonly').val("");
	});
	//jQuery("#myModal").fadeIn();
	
	jQuery(".button_close_modal").click(function(){		
		jQuery("#myModal").fadeOut();
	});
	
	jQuery(document).on('keydown', function(event) {
       if (event.key == "Escape") {
           jQuery("#myModal").fadeOut();
       }
   });

   
	
	new DataTable('.datatable.daily_kharch_report', {
		searching: true,
		lengthChange: true,
		info: true,
		 stateSave: true,
		initComplete: function () {
			
			var seelct_first = "Show All";
			
			this.api().columns().every(function (i) {
					var column = this;
					
					var order = this.order();
				    var header_this = this.column(i).header();
				    var title = jQuery(header_this).html();
					
	 				if('Action' != title){
						// Create select element
						var select = document.createElement('select');
						select.add(new Option(seelct_first));
						select.className = "form-control";
						
						// Apply listener for user change in value
						select.addEventListener('change', function () {
							var val = DataTable.util.escapeRegex(select.value);	 
							if(val == seelct_first){
								column.search('',true, false).draw();
							}else{
								column.search(val ? '^' + val + '$' : '', true, false).draw();
							}
							
						});
						
						var label_filed = document.createElement('label');
						label_filed.innerHTML = title;
						label_filed.className = "";
						
						var field_div = document.createElement('div')
						field_div.className = "";
						field_div.append(select);
						
						var form_group = document.createElement('div');
						form_group.className = "form-group col-xl-2 col-md-4 col-sm-6 col-12";
						form_group.append(label_filed);
						form_group.append(field_div);
						
						jQuery(".custom_diynamic_filters").append(form_group);
						
						// Add list of options
						if('Amount' == title){
							var list = new Array();
							var i = 0;
							
							column.data().unique().sort().each(function (d, j) {
								list[i] = d;
								i++;
							});
													
							list.sort(function(a, b){return a-b});
							
							jQuery(list).each(function (d, j) {
								select.add(new Option(j));
							});
						}else{
							column.data().unique().sort().each(function (d, j) {
								select.add(new Option(d));
							});
						}
					}
				});
		}
	});
});