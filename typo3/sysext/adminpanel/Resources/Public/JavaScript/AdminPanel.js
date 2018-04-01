function recalculateContentHeightForAdminPanel() {
	var w = window,
		d = document,
		e = d.documentElement,
		g = d.getElementsByTagName('body')[0],
		y = w.innerHeight || e.clientHeight || g.clientHeight;

	Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-module-content]')).forEach(function (element) {
		element.style.maxHeight = y - 36 + 'px';
	});
}

function sendAdminPanelForm(event) {
	event.preventDefault();
	var typo3AdminPanel = document.querySelector('[data-typo3-role=typo3-adminPanel]');
	var formData = new FormData(typo3AdminPanel.querySelector('form'));
	var request = new XMLHttpRequest();
	request.open("POST", typo3AdminPanel.dataset.typo3AjaxUrl);
	request.send(formData);
	request.onload = function () {
		location.reload();
	};
}

function toggleAdminPanelState() {
	var request = new XMLHttpRequest();
	request.open("GET", this.dataset.typo3AjaxUrl);
	request.send();
	request.onload = function () {
		location.reload();
	};
}

function initializeAdminPanel() {
	recalculateContentHeightForAdminPanel();
	Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-saveButton]')).forEach(function (elm) {
		elm.addEventListener('click', sendAdminPanelForm);
	});

	Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-trigger]')).forEach(function(trigger) {
		trigger.addEventListener('click', toggleAdminPanelState);
	});
}

window.addEventListener('load', initializeAdminPanel, false);
window.addEventListener('resize', recalculateContentHeightForAdminPanel, false);
