/*!
 * long-press-event - v2.4.2
 * Pure JavaScript long-press-event
 * https://github.com/john-doherty/long-press-event
 * @author John Doherty <www.johndoherty.info>
 * @license MIT
 */
!function(e,t){"use strict";var n=null,a="ontouchstart"in e||navigator.MaxTouchPoints>0||navigator.msMaxTouchPoints>0,i=a?"touchstart":"mousedown",o=a?"touchend":"mouseup",m=a?"touchmove":"mousemove",r=0,u=0,s=10,c=10;function l(e){var n;d(),n=e,e=a&&n.touches&&n.touches[0]?n.touches[0]:n,this.dispatchEvent(new CustomEvent("long-press",{bubbles:!0,cancelable:!0,detail:{clientX:e.clientX,clientY:e.clientY},clientX:e.clientX,clientY:e.clientY,offsetX:e.offsetX,offsetY:e.offsetY,pageX:e.pageX,pageY:e.pageY,screenX:e.screenX,screenY:e.screenY}))||t.addEventListener("click",function e(n){t.removeEventListener("click",e,!0),function(e){e.stopImmediatePropagation(),e.preventDefault(),e.stopPropagation()}(n)},!0)}function v(a){d(a);var i=a.target,o=parseInt(function(e,n,a){for(;e&&e!==t.documentElement;){var i=e.getAttribute(n);if(i)return i;e=e.parentNode}return a}(i,"data-long-press-delay","1500"),10);n=function(t,n){if(!(e.requestAnimationFrame||e.webkitRequestAnimationFrame||e.mozRequestAnimationFrame&&e.mozCancelRequestAnimationFrame||e.oRequestAnimationFrame||e.msRequestAnimationFrame))return e.setTimeout(t,n);var a=(new Date).getTime(),i={},o=function(){(new Date).getTime()-a>=n?t.call():i.value=requestAnimFrame(o)};return i.value=requestAnimFrame(o),i}(l.bind(i,a),o)}function d(t){var a;(a=n)&&(e.cancelAnimationFrame?e.cancelAnimationFrame(a.value):e.webkitCancelAnimationFrame?e.webkitCancelAnimationFrame(a.value):e.webkitCancelRequestAnimationFrame?e.webkitCancelRequestAnimationFrame(a.value):e.mozCancelRequestAnimationFrame?e.mozCancelRequestAnimationFrame(a.value):e.oCancelRequestAnimationFrame?e.oCancelRequestAnimationFrame(a.value):e.msCancelRequestAnimationFrame?e.msCancelRequestAnimationFrame(a.value):clearTimeout(a)),n=null}"function"!=typeof e.CustomEvent&&(e.CustomEvent=function(e,n){n=n||{bubbles:!1,cancelable:!1,detail:void 0};var a=t.createEvent("CustomEvent");return a.initCustomEvent(e,n.bubbles,n.cancelable,n.detail),a},e.CustomEvent.prototype=e.Event.prototype),e.requestAnimFrame=e.requestAnimationFrame||e.webkitRequestAnimationFrame||e.mozRequestAnimationFrame||e.oRequestAnimationFrame||e.msRequestAnimationFrame||function(t){e.setTimeout(t,1e3/60)},t.addEventListener(o,d,!0),t.addEventListener(m,function(e){var t=Math.abs(r-e.clientX),n=Math.abs(u-e.clientY);(t>=s||n>=c)&&d()},!0),t.addEventListener("wheel",d,!0),t.addEventListener("scroll",d,!0),t.addEventListener(i,function(e){r=e.clientX,u=e.clientY,v(e)},!0)}(window,document);

// when document ready
$(document).ready(function(){
	// add select on start
	$('select').formSelect();
	$('.modal').modal();

	// listen for the long-press event
	$('.long-press').each(function() {
		this.addEventListener('long-press', function(e) {
			$('#favoriteModal').attr('data-value', $(this).attr('id'));
			$('#favoriteService').html($(this).attr('data-value'));
			$('#favoriteDesc').html($(this).attr('data-description'));
			$('#favoriteSwitch').prop('checked', $(this).hasClass('favorite'));
			$('#favoriteModal').modal('open');
		});
	});

	// filter services
	var selectedCategory = '';
	if(typeof favorites != 'undefined') {
		var filter = favorites ? 'favoritos' : 'todos';
		filtrar(filter);
	}
});

// filter by service category
function filtrar(category) {
	// select category
	selectedCategory = category;

	// highlight the category
	$('.filter').addClass('hidden');
	$('#'+category).find('.filter').removeClass('hidden');

	// scroll to the filters
	$('html, body').animate({scrollTop: $('#filters').offset().top}, 1000);

	// show msg only on the favorite category
	$('#empty-note').hide();

	// show the search bar
	if(category == 'buscar') {
		$('.service').slideDown('fast');
		$('#todosBtn').hide();
		$('#buscar input').val('').focus();
		return false;
	}

	// show favorites
	if(category == 'favoritos') {
		$('.service').hide();
		$('.favorite').slideDown('fast');
		updateFavoriteCount();
		return false;
	}

	// show all categories
	if(category == 'todos') {
		$('.service').slideDown('fast');
		$('#todosBtn').hide();
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
	var text = cleanUpSpecialChars($('#buscar input').val().toLowerCase());

	$('.service').not('#todosBtn').show().each(function(i, e) {
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
function toogleWidget(e) {
	// get the check object
	var widget = $(e).find('.check');

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

	// submit widgets to be saved
	apretaste.send({
		command: 'INICIO SALVAR',
		data: {'widgets': widgets}
	});
}

// save favorite
function toggleFavorite(service) {
	// submit favorite
	apretaste.send({
		command: 'INICIO FAVORITO',
		data: {'service': service},
		redirect: false,
		showLoading: false
	});

	// show or hide icon if you are in the favorites tab
	if(selectedCategory == 'favoritos') {
		if($('#'+service).hasClass('favorite')) $('#'+service).fadeOut();
		else $('#'+service).fadeIn();
	}

	// change the favorite class
	$('#'+service).toggleClass('favorite');

	// update favoritos count
	updateFavoriteCount();
}

// update favoritos count
function updateFavoriteCount() {
	// get the favorite count
	var favoriteCount = $('.favorite').not('#todosBtn').length;

	// update the counter
	$('#favorite-count').html(favoriteCount);

	// show/hide message
	if(favoriteCount > 0) $('#empty-note').hide();
	else {
		$('#empty-note').show();
		$('#todosBtn').hide();
	}
}
