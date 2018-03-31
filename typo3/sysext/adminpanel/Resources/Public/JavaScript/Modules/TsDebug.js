function initializeTsDebug() {
	var button = document.getElementById('typo3-adminPanel-tsdebug-save-form');
	button.addEventListener('click', sendAdminPanelForm);
}

window.addEventListener('load', initializeTsDebug, false);
