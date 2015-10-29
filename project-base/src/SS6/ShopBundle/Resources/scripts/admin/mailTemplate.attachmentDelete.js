(function ($){

	SS6 = SS6 || {};
	SS6.mailTemplate = SS6.mailTemplate || {};

	SS6.mailTemplate.AttachmentDelete = function ($attachment) {
		var $deleteButton = $attachment.find('.js-mail-template-attachment-delete-button');
		var $revertButton = $attachment.find('.js-mail-template-attachment-delete-revert-button');
		var $revert = $attachment.find('.js-mail-template-attachment-delete-revert');
		var $checkbox = $attachment.find('.js-mail-template-attachment-delete-checkbox');

		this.init = function() {
			$deleteButton.click(deleteButtonClick);
			$revertButton.click(revertButtonClick);
			updateState();
		};

		var deleteButtonClick = function() {
			$checkbox.prop('checked', true);
			updateState();
		};

		var revertButtonClick = function() {
			$checkbox.prop('checked', false);
			updateState();
		};

		var updateState = function() {
			var isChecked = $checkbox.prop('checked');
			$deleteButton.toggle(!isChecked);
			$revert.toggle(isChecked);
		}
	};

	$(document).ready(function () {
		$('.js-mail-template-attachment').each(function () {
			var attachmentDelete = new SS6.mailTemplate.AttachmentDelete($(this));
			attachmentDelete.init();
		});
	});

})(jQuery);
