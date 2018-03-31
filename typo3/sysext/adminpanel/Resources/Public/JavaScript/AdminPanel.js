function recalculateContentHeightForAdminPanel() {
	var w = window,
		d = document,
		e = d.documentElement,
		g = d.getElementsByTagName('body')[0],
		y = w.innerHeight || e.clientHeight || g.clientHeight;

	Array.from(document.getElementsByClassName('typo3-adminPanel-module-content')).forEach(function (element) {
		element.style.maxHeight = y - 36 + 'px';
	});
}

function sendAdminPanelForm(event) {
	event.preventDefault();
	var formData = new FormData(document.getElementById('typo3-adminPanel-form'));
	var request = new XMLHttpRequest();
	request.open("POST", typo3AdminPanelSaveUrl);
	request.send(formData);
	request.onload = function () {
		location.reload();
	};
}

function initializeAdminPanel() {
	recalculateContentHeightForAdminPanel();
	var button = document.getElementById('typo3-adminPanel-save-form');
	button.addEventListener('click', sendAdminPanelForm);
}

window.addEventListener('load', initializeAdminPanel, false);
window.addEventListener('resize', recalculateContentHeightForAdminPanel, false);
