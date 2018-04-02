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
  initTabs();
  Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-saveButton]')).forEach(function (elm) {
    elm.addEventListener('click', sendAdminPanelForm);
  });

  Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-trigger]')).forEach(function (trigger) {
    trigger.addEventListener('click', toggleAdminPanelState);
  });

  var allTriggers = Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-module-trigger]'));

  allTriggers.forEach(function (elm) {
    elm.addEventListener('click', function () {
      if (this.classList.contains('active')) {
        this.classList.remove('active');
      } else {
        allTriggers.forEach(function (innerElm) {
          innerElm.classList.remove('active');
        });
        this.classList.add('active');
      }
    });
  });
}

function initTabs() {

  var myTabs = document.querySelectorAll("[data-typo3-role=typo3-adminPanel-tabs] > li");

  function myTabClicks(tabClickEvent) {
    tabClickEvent.preventDefault();

    for (var i = 0; i < myTabs.length; i++) {
      myTabs[i].classList.remove("active");
    }

    var clickedTab = tabClickEvent.currentTarget;
    clickedTab.classList.add("active");
    var myContentPanes = document.querySelectorAll("[data-typo3-role=typo3-adminPanel-tab]");

    for (var j = 0; j < myContentPanes.length; j++) {
      myContentPanes[j].classList.remove("active");
    }

    var activePaneId = clickedTab.dataset.typo3TabTarget;
    var activePane = document.querySelector('[data-typo3-tab-id=' + activePaneId + ']');

    activePane.classList.add("active");

  }

  for (var i = 0; i < myTabs.length; i++) {
    myTabs[i].addEventListener("click", myTabClicks)
  }

}

window.addEventListener('load', initializeAdminPanel, false);
window.addEventListener('resize', recalculateContentHeightForAdminPanel, false);
