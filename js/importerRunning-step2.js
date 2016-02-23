var xml_filename, xml_file_count, xml_current_key
$(document).ready(function(){
	$("#step2form").on('submit',step2go)
	$("#reset_xml_line").on('click',resetLineCount)
	$("#test_import").on('click',testImport)
});

step2go = function(event){
	event.preventDefault();
	theloopXml(this);
}
testImport = function() {
	$.ajax({
		url: $(this).data('url'),
		data: {
			ajax: true,
			action: 'testImport'
		},
		method: "POST",
		dataType: 'json',
		success: function(data){
			console.log('SUCCESS '+ JSON.stringify(data))
			$("#error_stant").html(data)
		},
		error: function(data){
			console.log('WARNING ')
			$("#error_stant").html(data.responseText)
		}
	});
}
theloopXml = function(form) {
	//console.log($(form).attr('action'));
	$.ajax({
		url: $(form).attr('action'),
		data: {
			ajax: true,
			action: 'transfertXml'
		},
		method: "POST",
		dataType: 'json',
		success: function(data){
			console.log('Success!!! - '+JSON.stringify(data))
			if(data.status !== undefined){
				if(data.status == 'loop_end')
					console.log('End of loop');//On rajoute des boutons pour passer à la page suivante
				else if (data.status == 'looping')
				{

					xml_current_key = data.current_key_in_xml
					updateStat()
					injectInHtml(xml_current_key,data.product);
					theloopXml(form)
				}
			} else {
				console.log('WARNING - il n\'y a plus de status dans retour de l\'appel.')
			}
		},
		error: function(data) {
			console.log('Error')
			$("#error_stant").html(data.responseText)
		}
	})
}
injectInHtml = function(xml_key,product) {
	// Fonction pour injecter le contenu dans le site
	console.log(product)
	$row = $('<tr>')
	$row.append('<td>'+xml_key+'</td>')
		.append('<td>'+product.id+'</td>')
		.append('<td>'+product.artnr+'</td>')
		.append('<td>'+product.title+'</td>')
		.append('<td>'+product.price.b2b+'</td>')
		.append('<td>'+product.price.b2c+'</td>')
		.append('<td>'+product.date+'</td>')
		.append('<td>'+product.modifydate+'</td>')
		.append('<td><img src="http://cdn.edc-internet.nl/100/'+product.artnr+'.jpg"></tr>')
	$row.appendTo($('#product_table'));
}
//Update stats
updateStat = function() {
	$("#xml_current_key").html(xml_current_key);
}
resetLineCount = function() {
	$.ajax({
		url: $(this).data('url'),
		data: {
			ajax: 	true,
			action: 'resetXmlCountLine'
		},
		method: 'POST',
		dataType: 'json',
		success: function(data){
			console.log('resetXmlCountLine - Success - ' +JSON.stringify(data));
			xml_current_key = data.current_key_in_xml
			updateStat()
		},
		error: function(data) {
			console.log('Impossible de resetter le xml_line_count')
		}
	})
}