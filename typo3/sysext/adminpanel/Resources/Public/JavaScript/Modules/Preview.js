
function initializePreviewModule() {
	var dateField = document.getElementById('typo3-adminPanel-preview-date-hr');
	var timeField = document.getElementById('typo3-adminPanel-preview-time-hr');
	var targetField = document.getElementById(dateField.dataset.target);
	if (targetField.value) {
		var cd = new Date(targetField.value);
		document.getElementById('typo3-adminPanel-preview-date-hr').value = cd.getFullYear() + "-" + ((cd.getMonth()+1) < 10 ? '0' : '') + (cd.getMonth()+1) + "-" + (cd.getDate() < 10 ? '0' : '') + cd.getDate();
		document.getElementById('typo3-adminPanel-preview-time-hr').value = (cd.getHours() < 10 ? '0' : '') + cd.getHours() + ":" + (cd.getMinutes() < 10 ? '0' : '') + cd.getMinutes();
	}

	var updateDateField = function () {
		var dateVal = document.getElementById('typo3-adminPanel-preview-date-hr').value;
		var timeVal = document.getElementById('typo3-adminPanel-preview-time-hr').value;
		if (!dateVal && timeVal) {
			var tempDate = new Date();
			dateVal = tempDate.getDate() + "-" + tempDate.getMonth() + "-" + tempDate.getFullYear();
		}
		if (dateVal && !timeVal) {
			timeVal =  "00:00";
		}

		if(!dateVal && !timeVal) {
			targetField.value = "";
		} else {
			var stringDate = dateVal + " " + timeVal;
			var date = new Date(stringDate);
			targetField.value = date.toISOString();
		}
	};
	dateField.addEventListener('change', updateDateField);
	timeField.addEventListener('change', updateDateField);
}

window.addEventListener('load', initializePreviewModule, false);