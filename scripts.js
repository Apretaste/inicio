$(document).ready(function(){
	$('select').formSelect();
});

// filter by service category
function filtrar(e) {
	// get the category
	var category = $(e).attr('data');

	// highlight the category
	$('.filter').addClass('hidden');
	$(e).find('.filter').removeClass('hidden');

	// show the search bar
	if(category == 'buscar') {
		$('.service').slideDown('fast');
		$('#buscar').focus();
		return false;
	}

	// show all categories
	if(category == 'todos') {
		$('.service').slideDown('fast');
	}

	// filter by category
	else {
		$('.service').hide();
		$('.'+category).slideDown('fast');		
	}
}

// search for a service on the list
function buscar() {
	// get text to search by
	var text = cleanUpSpecialChars($('#buscar').val().toLowerCase());

	$('.service').show().each(function(i, e) {
		// get the caption
		var caption = cleanUpSpecialChars($(e).attr('data-value').toLowerCase());

		// hide if caption does not match
		if(caption.indexOf(text) < 0) {
			$(e).hide();
		}
	})
}

// clean special chars
function cleanUpSpecialChars(str) {
	return str
		.replace(/Á/g,"A").replace(/a/g,"a")
		.replace(/É/g,"E").replace(/é/g,"e")
		.replace(/Í/g,"I").replace(/í/g,"i")
		.replace(/Ó/g,"O").replace(/ó/g,"o")
		.replace(/Ú/g,"U").replace(/ú/g,"u")
		.replace(/Ñ/g,"N").replace(/ñ/g,"n")
		.replace(/[^a-z0-9]/gi,''); // final clean up
}

// activate or deactivate widget
function toogleWidget(widget) {
	// change the icon
	if($(widget).hasClass('fa-check-circle')) {
		$(widget).removeClass('fa-check-circle').addClass('fa-circle');
	} else {
		$(widget).removeClass('fa-circle').addClass('fa-check-circle');
	}
}

// save widgets
function widgetSave() {
	// get widgets to save
	var widgets = [];
	$('.widget-action').each(function() {
		var widget = $(this);
		if($(widget).hasClass('fa-check-circle')) {
			widgets.push(widget.attr('data'));
		}
	});

	// get favorite services
	var favorites = $('#favoritos').val().slice(0, 4).join(',');

	// submit widgets to be saved
	apretaste.send({
		command: 'INICIO SALVAR',
		data: {
			'widgets': widgets,
			'favorites': favorites
		}
	});
}
