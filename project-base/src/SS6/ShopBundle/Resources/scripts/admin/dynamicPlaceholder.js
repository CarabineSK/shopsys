(function ($) {

	SS6 = window.SS6 || {};
	SS6.dynamicPlaceholder = SS6.dynamicPlaceholder || {};

	SS6.dynamicPlaceholder.DynamicPlaceholder = function ($input) {
		var self = this;
		var $sourceInput = $('#' + $input.data('placeholder-source-input-id'));

		this.init = function() {
			$sourceInput.change(function () {
				updatePlaceholder();
			});

			updatePlaceholder();
		};

		var updatePlaceholder = function () {
			$input.attr('placeholder', $sourceInput.val());
			$input.trigger('placeholderChange');
		};
	};

	$(document).ready(function () {
		$('.js-dynamic-placeholder').each(function () {
			var dynamicPlaceholder = new SS6.dynamicPlaceholder.DynamicPlaceholder($(this));
			dynamicPlaceholder.init();
		});
	});

})(jQuery);
