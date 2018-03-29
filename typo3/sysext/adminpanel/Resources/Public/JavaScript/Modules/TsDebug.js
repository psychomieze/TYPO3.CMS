function initializeTsDebug() {
  var button = document.getElementById('typo3-adminPanel-tsdebug-save-form');
  if (button.addEventListener) {
    button.addEventListener('click', sendAdminPanelForm);
  } else if (button.attachEvent) {
    button.attachEvent('onclick', sendAdminPanelForm);
  }
}

if (window.addEventListener) {
  window.addEventListener('load', initializeTsDebug, false);
} else if (window.attachEvent) {
  window.attachEvent('onload', initializeTsDebug);
}
