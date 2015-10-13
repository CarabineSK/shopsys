(function ($) {

	SS6 = window.SS6 || {};
	SS6.product = SS6.product || {};

	SS6.product.init = function () {
		var usingStockSelection = $('#product_edit_form_productData_usingStock input[type="radio"]');
		var $outOfStockActionSelection = $('select[name="product_edit_form[productData][outOfStockAction]"]');

		usingStockSelection.change(function () {
			SS6.product.toggleIsUsingStock($(this).val() === '1');
		});

		$outOfStockActionSelection.change(function () {
			SS6.product.toggleIsUsingAlternateAvailability($(this).val() === SS6.constant('\\SS6\\ShopBundle\\Model\\Product\\Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY'));
		});

		SS6.product.toggleIsUsingStock(usingStockSelection.filter(':checked').val() === '1');
		SS6.product.toggleIsUsingAlternateAvailability($outOfStockActionSelection.val() === SS6.constant('\\SS6\\ShopBundle\\Model\\Product\\Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY'));

		SS6.product.initializeSideNavigation();

		$('#js-close-without-saving').on('click', function () {
			window.close();
			return false;
		});

	};

	SS6.product.toggleIsUsingStock = function (isUsingStock) {
		$('.js-product-using-stock').toggle(isUsingStock);
		$('.js-product-not-using-stock').toggle(!isUsingStock);
	};

	SS6.product.toggleIsUsingAlternateAvailability = function (isUsingStockAndAlternateAvailability) {
		$('.js-product-using-stock-and-alternate-availability').toggle(isUsingStockAndAlternateAvailability);
	};

	SS6.product.initializeSideNavigation = function () {
		var $productDetailNavigation = $('.js-product-detail-navigation');
		$('.form-group__title, .form-full__title').each(function () {
			var $titleClone = $(this).clone();
			$titleClone.find('.js-validation-errors-list').remove();
			var $navigationItemLi = '<li class="side-menu__item">' + $titleClone.text() + '</li>';
			$productDetailNavigation.append($navigationItemLi);
		});
	};

	$(document).ready(function () {
		SS6.product.init();
	});

})(jQuery);
