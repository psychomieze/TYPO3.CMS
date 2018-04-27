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

function renderBackdrop() {
  var body = document.querySelector('body');
  var backdrop = document.createElement('div');
  backdrop.classList.add('typo3-adminPanel-backdrop');
  body.appendChild(backdrop);
  addBackdropListener();
}

function removeBackdrop() {
  var backdrop = document.querySelector('.typo3-adminPanel-backdrop');
  if (backdrop !== null) {
    backdrop.remove();
  }
}

function addBackdropListener() {
  var allBackdrops = Array.from(document.querySelectorAll('.typo3-adminPanel-backdrop'));
  allBackdrops.forEach(function (elm) {
    elm.addEventListener('click', function () {
      removeBackdrop();
      var allModules = Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-module-trigger]'));
      allModules.forEach(function (innerElm) {
        innerElm.closest('.typo3-adminPanel-module').classList.remove('active');
      });
    });
  });
}

function addModuleListener(allModules) {
  allModules.forEach(function (elm) {
    elm.addEventListener('click', function () {
      var parent = this.closest('.typo3-adminPanel-module');
      if (parent.classList.contains('active')) {
        removeBackdrop();
        parent.classList.remove('active');
      } else {
        allModules.forEach(function (innerElm) {
          removeBackdrop();
          innerElm.closest('.typo3-adminPanel-module').classList.remove('active');
        });
        if (parent.classList.contains('typo3-adminPanel-module-backdrop')) {
          renderBackdrop();
        }
        parent.classList.add('active');
      }
    });
  });
}

function initializeAdminPanel() {
  var allModules = Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-module-trigger]'));
  addModuleListener(allModules);
  initTabs();


  Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-saveButton]')).forEach(function (elm) {
    elm.addEventListener('click', sendAdminPanelForm);
  });

  Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-trigger]')).forEach(function (trigger) {
    trigger.addEventListener('click', toggleAdminPanelState);
  });

  var tabTriggers = Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-tabs-trigger]'));
  tabTriggers.forEach(function (elm) {
    elm.addEventListener('click', function () {
      var targets = this.closest('.typo3-adminPanel-module-content-header').querySelectorAll('.typo3-adminPanel-module-content-nav');
      targets.forEach(function (target) {
        if (target.classList.contains('active')) {
          target.classList.remove('active');
        } else {
          target.classList.add('active');
        }
      });
    });
  });

  var popupTriggers = Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-popup-trigger]'));
  popupTriggers.forEach(function (elm) {
    elm.addEventListener('click', function () {
      if (this.classList.contains('active')) {
        this.classList.remove('active');
      } else {
        popupTriggers.forEach(function (innerElm) {
          innerElm.classList.remove('active');
        });
        this.classList.add('active');
      }
    });
  });

  var panelTriggers = Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-panel-trigger]'));
  panelTriggers.forEach(function (elm) {
    elm.addEventListener('click', function () {
      var target = this.closest('.typo3-adminPanel-panel');
      if (target.classList.contains('active')) {
        target.classList.remove('active');
      } else {
        target.classList.add('active');
      }
    });
  });

  var moduleClose = Array.from(document.querySelectorAll('[data-typo3-role=typo3-adminPanel-module-close]'));
  moduleClose.forEach(function (elm) {
    elm.addEventListener('click', function () {
      allModules.forEach(function (innerElm) {
        innerElm.closest('.typo3-adminPanel-module').classList.remove('active');
      });
      removeBackdrop();
    });
  });

  var dataFields = Array.from(document.querySelectorAll('.typo3-adminPanel-table th, .typo3-adminPanel-table td'));
  dataFields.forEach(function (elm) {
    elm.addEventListener('click', function () {
      elm.focus();
      // elm.select();

      try {
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful';
        console.log('Copying text command was ' + msg);
      } catch (err) {
        console.log('Oops, unable to copy');
      }
    });
  });

  addBackdropListener();
}

function initTabs() {

  var myTabs = document.querySelectorAll("[data-typo3-role=typo3-adminPanel-tabs] > a");

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
