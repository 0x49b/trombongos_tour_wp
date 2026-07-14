(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		initSelectAll();
		initAutoSubmit();
		initConfirmForms();
		initCategoryPage();
		initEventForm();
		initEventsBulk();
		initImporter();
	});

	function initAutoSubmit() {
		document.querySelectorAll('[data-tour-auto-submit]').forEach(function (element) {
			element.addEventListener('change', function () {
				if (element.form) {
					element.form.submit();
				}
			});
		});
	}

	function initConfirmForms() {
		document.querySelectorAll('form[data-tour-confirm]').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				var message = form.getAttribute('data-tour-confirm');
				if (message && !window.confirm(message)) {
					event.preventDefault();
				}
			});
		});
	}

	function initSelectAll() {
		document.querySelectorAll('[data-tour-select-all]').forEach(function (selectAll) {
			var checkboxClass = selectAll.getAttribute('data-tour-select-all');

			selectAll.addEventListener('change', function () {
				document.querySelectorAll('.' + checkboxClass).forEach(function (checkbox) {
					checkbox.checked = selectAll.checked;
				});
			});
		});
	}

	function initCategoryPage() {
		var modal = document.getElementById('copy-category-modal');
		if (!modal) {
			return;
		}

		document.querySelectorAll('[data-tour-copy-category]').forEach(function (button) {
			button.addEventListener('click', function () {
				document.getElementById('copy-category-id').value = button.getAttribute('data-id');
				document.getElementById('copy-category-name').textContent = button.getAttribute('data-name');
				modal.classList.add('is-open');
			});
		});

		modal.querySelectorAll('[data-tour-modal-close]').forEach(function (button) {
			button.addEventListener('click', function () {
				modal.classList.remove('is-open');
			});
		});

		modal.addEventListener('click', function (event) {
			if (event.target === modal) {
				modal.classList.remove('is-open');
			}
		});

		var bulkSelector = document.getElementById('bulk-action-selector');
		var targetSeasonSelector = document.getElementById('target-season-selector');

		function toggleBulkCategoryControls() {
			if (!bulkSelector || !targetSeasonSelector) {
				return;
			}

			targetSeasonSelector.style.display = bulkSelector.value === 'bulk_copy' ? '' : 'none';
		}

		if (bulkSelector) {
			bulkSelector.addEventListener('change', toggleBulkCategoryControls);
			toggleBulkCategoryControls();
		}

		var bulkForm = document.getElementById('categories-bulk-form');
		var bulkButton = bulkForm ? bulkForm.querySelector('[data-tour-bulk-apply]') : null;

		if (bulkButton) {
			bulkButton.addEventListener('click', function (event) {
				if (!applyCategoryBulkAction()) {
					event.preventDefault();
				}
			});
		}
	}

	function applyCategoryBulkAction() {
		var action = document.getElementById('bulk-action-selector').value;
		if (!action) {
			window.alert('Bitte wählen Sie eine Aktion aus.');
			return false;
		}

		var checked = document.querySelectorAll('.category-checkbox:checked');
		if (checked.length === 0) {
			window.alert('Bitte wählen Sie mindestens eine Kategorie aus.');
			return false;
		}

		if (action === 'bulk_copy') {
			var targetSeason = document.getElementById('target-season-selector').value;
			if (!targetSeason) {
				window.alert('Bitte wählen Sie eine Ziel-Saison aus.');
				return false;
			}
			if (!window.confirm('Möchten Sie ' + checked.length + ' Kategorie(n) in die ausgewählte Saison kopieren?')) {
				return false;
			}
		} else if (action === 'bulk_delete') {
			if (!window.confirm('Sind Sie sicher, dass Sie ' + checked.length + ' ausgewählte Kategorie(n) löschen möchten? Kategorien mit Auftritten werden nicht gelöscht.')) {
				return false;
			}
		}

		appendHiddenAction('categories-bulk-form', 'tour_category_action', action);
		return true;
	}

	function initEventForm() {
		var dateInput = document.getElementById('event_date');
		var daySelect = document.getElementById('day');
		var playInput = document.getElementById('play');
		var gatheringInput = document.getElementById('gathering');
		var locationInput = document.getElementById('location');
		var mapsPreview = document.getElementById('maps-preview');
		var mapsLink = document.getElementById('maps-link');

		if (dateInput && daySelect) {
			dateInput.addEventListener('change', function () {
				updateDayFromDate(dateInput, daySelect);
			});
		}

		if (playInput && gatheringInput) {
			var updateGathering = function () {
				updateGatheringFromPlay(playInput, gatheringInput);
			};
			playInput.addEventListener('change', updateGathering);
			playInput.addEventListener('input', updateGathering);
		}

		if (locationInput && mapsPreview && mapsLink) {
			var updateMaps = function () {
				updateMapsLink(locationInput, mapsPreview, mapsLink);
			};
			locationInput.addEventListener('input', updateMaps);
			locationInput.addEventListener('change', updateMaps);
			updateMaps();
		}
	}

	function updateDayFromDate(dateInput, daySelect) {
		if (!dateInput.value) {
			return;
		}

		var dateObj = new Date(dateInput.value + 'T00:00:00');
		var dayNum = dateObj.getDay();
		dayNum = dayNum === 0 ? 6 : dayNum - 1;
		daySelect.value = dayNum;
	}

	function updateGatheringFromPlay(playInput, gatheringInput) {
		if (!playInput.value) {
			return;
		}

		var parts = playInput.value.split(':').map(Number);
		var hours = parts[0];
		var minutes = parts[1];
		var totalMinutes = hours * 60 + minutes - 15;

		if (totalMinutes < 0) {
			totalMinutes += 24 * 60;
		}

		var newHours = Math.floor(totalMinutes / 60);
		var newMinutes = totalMinutes % 60;
		var remainder = newMinutes % 15;

		if (remainder <= 7) {
			newMinutes -= remainder;
		} else {
			newMinutes += 15 - remainder;
			if (newMinutes >= 60) {
				newMinutes = 0;
				newHours++;
				if (newHours >= 24) {
					newHours = 0;
				}
			}
		}

		gatheringInput.value = String(newHours).padStart(2, '0') + ':' + String(newMinutes).padStart(2, '0');
	}

	function updateMapsLink(locationInput, mapsPreview, mapsLink) {
		var location = locationInput.value.trim();

		if (location) {
			mapsLink.href = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(location);
			mapsPreview.style.display = 'block';
		} else {
			mapsPreview.style.display = 'none';
		}
	}

	function initEventsBulk() {
		var bulkButton = document.querySelector('[data-tour-events-bulk-apply]');
		if (!bulkButton) {
			return;
		}

		bulkButton.addEventListener('click', function (event) {
			if (!applyEventsBulkAction()) {
				event.preventDefault();
			}
		});
	}

	function applyEventsBulkAction() {
		var action = document.getElementById('bulk-action-selector-top').value;
		if (!action) {
			window.alert('Bitte wählen Sie eine Aktion aus.');
			return false;
		}

		var checked = document.querySelectorAll('.event-checkbox:checked');
		if (checked.length === 0) {
			window.alert('Bitte wählen Sie mindestens einen Auftritt aus.');
			return false;
		}

		if (action === 'bulk_delete') {
			if (!window.confirm('Sind Sie sicher, dass Sie die ausgewählten Auftritte löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.')) {
				return false;
			}
		}

		appendHiddenAction('events-list-form', 'tour_event_action', action);
		return true;
	}

	function initImporter() {
		var seasonSelect = document.getElementById('season_id');
		var newSeasonWrapper = document.getElementById('new_season_wrapper');

		if (!seasonSelect || !newSeasonWrapper) {
			return;
		}

		var toggleNewSeason = function () {
			newSeasonWrapper.style.display = seasonSelect.value === '0' ? 'block' : 'none';
		};

		seasonSelect.addEventListener('change', toggleNewSeason);
		toggleNewSeason();
	}

	function appendHiddenAction(formId, fieldName, value) {
		var form = document.getElementById(formId);
		if (!form) {
			return;
		}

		var actionInput = document.createElement('input');
		actionInput.type = 'hidden';
		actionInput.name = fieldName;
		actionInput.value = value;
		form.appendChild(actionInput);
	}
})();
