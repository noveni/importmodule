$(document).ready(function(){
	$("#step2form").on('submit',step2go)
});

step2go = function(event){
	event.preventDefault();
	theloopXml(this);
	// $.ajax({
	// 	url:$(this).attr('action'),
	// 	data: {
	// 		ajax: true,
	// 		action: 'transfertXml'
	// 	},
	// 	method: "POST",
	// 	dataType: 'json',
	// 	success: function(data) {
	// 		console.log('Success!!! - '+JSON.stringify(data))
	// 	},
	// 	error: function(data) {
	// 		console.log('Error')
	// 	}
	// })
}

theloopXml = function(form)
{
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
			theloopXml(form)
		},
		error: function(data) {
			console.log('Error')
		}
	})
}

//Comment je fais une belle boucle éfficace pour qu'elle boucle sur un tableau xml qui est lu dans un script php