function recalculateContentHeightForAdminPanel() {
  var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    x = w.innerWidth || e.clientWidth || g.clientWidth,
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
  if (button.addEventListener) {
    button.addEventListener('click', sendAdminPanelForm);
  } else if (button.attachEvent) {
    button.attachEvent('onclick', sendAdminPanelForm);
  }
}

if (window.addEventListener) {
  window.addEventListener('load', initializeAdminPanel, false);
  window.addEventListener('resize', recalculateContentHeightForAdminPanel, false);
} else if (window.attachEvent) {
  window.attachEvent('onload', initializeAdminPanel);
  window.attachEvent('onresize', recalculateContentHeightForAdminPanel);
}
