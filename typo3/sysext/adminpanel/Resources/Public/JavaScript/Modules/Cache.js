function initializeCacheModule() {
	var buttons = Array.from(document.querySelectorAll('[data-typo3-adminpanel-cache-ajax-url]'));

	buttons.forEach(function (elem) {
		if (elem.addEventListener) {
			elem.addEventListener('click', clearCache);
		} else if (elem.attachEvent) {
			elem.addEventListener('click', clearCache);
		}
	});
}

function clearCache() {
	var url = this.dataset.typo3AdminpanelCacheAjaxUrl;
	var request = new XMLHttpRequest();
	request.open("GET", url);
	request.send();
	request.onload = function () {
		location.reload();
	};
}

window.addEventListener('load', initializeCacheModule, false);
