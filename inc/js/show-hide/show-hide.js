/*
Maf080720: em qualquer elemento da tela: nao se pode atribuir um handler de forma global)
remover esta biblioteca, porque ela atribui a todos os elementos da tela um handler para 
o método click

// Show an element
var show = function (elem) {

	// Get the natural height of the element
	var getHeight = function () {
		elem.style.display = 'block'; // Make it visible
		var height = elem.scrollHeight + 'px'; // Get it's height
		elem.style.display = ''; //  Hide it again
		return height;
	};

	var height = getHeight(); // Get the natural height
	elem.classList.add('is-visible'); // Make the element visible
	elem.style.height = height; // Update the max-height

	// Once the transition is complete, remove the inline max-height so the content can scale responsively
	window.setTimeout(function () {
		elem.style.height = '';
	}, 350);

};

// Hide an element
var hide = function (elem) {

	// Give the element a height to change from
	elem.style.height = elem.scrollHeight + 'px';

	// Set the height back to 0
	window.setTimeout(function () {
		elem.style.height = '0';
	}, 1);

	// When the transition is complete, hide it
	window.setTimeout(function () {
		elem.classList.remove('is-visible');
	}, 350);

};

// Toggle element visibility
var toggle = function (elem, id, timing) {

	// If the element is visible, hide it
	if (elem.classList.contains('is-visible') && (id == 'fechar')) {
		hide(elem);
		return;
	}

	// Otherwise, show it
	show(elem);
	
};

// Listen for click events 
document.addEventListener('click', function (event) {
	
	var target = event.target;

  while (target && target.tagName !== 'A') {
    target = target.parentNode;
    if (!target) { return; }
  }
	// Make sure clicked element is our toggle
	if (!target.classList.contains('toggle')) return;

	// Prevent default link behavior
	event.preventDefault();

	// Get the content
	var content = document.querySelector(target.hash);

	if (!content) return;

	// Toggle the content
	toggle(content, target.id);

}, false);
*/