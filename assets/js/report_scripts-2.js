jQuery(document).ready(function(){
	new DataTable('.daily_kharch_report', {
		stateSave: true,
		responsive: true,
		searching: false,
		info: false,
		pageLength: false,
		//paging: false,
		lengthChange: false,
		columnDefs: [
			{
				targets: [0],
				orderData: [0, 1]
			},
			{
				targets: [1],
				orderData: [1, 1]
			},
			{
				targets: [2],
				orderData: [2, 1]
			}
		],
		initComplete: function () {
			
			var seelct_first = "Show All";
			
			this.api().columns().every(function (i) {
					var column = this;
					
					var order = this.order();
				    var header_this = this.column(i).header();
				    var title = jQuery(header_this).html();
	 				
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
					form_group.className = "form-group col-md-4";
					form_group.append(label_filed);
					form_group.append(field_div);
					
					jQuery(".custom_diynamic_filters").append(form_group);
	 
					// Add list of options
					column.data().unique().sort().each(function (d, j) {
						select.add(new Option(d));
					});
				});
		}
	});
});