(function ($) {

	SS6 = window.SS6 || {};
	SS6.productsPicker = SS6.productsPicker || {};

	SS6.productsPicker.instances = [];

	SS6.productsPicker.close = function () {
		$.magnificPopup.close();
	};

	SS6.productsPicker.init = function () {
		$('.js-products-picker').each(function () {
			var productsPicker = new SS6.productsPicker.ProductsPicker($(this));
			productsPicker.init();
		});
	};

	SS6.productsPicker.ProductsPicker = function ($productsPicker) {
		var self = this;
		var instanceId = SS6.productsPicker.instances.length;
		SS6.productsPicker.instances[instanceId] = this;

		var $addButton = $productsPicker.find('.js-products-picker-button-add');
		var $itemsContainer = $productsPicker.find('.js-products-picker-items');
		var productItems = [];
		var mainProductId = $productsPicker.data('products-picker-main-product-id');

		this.init = function () {
			$addButton.click(openProductsPickerWindow);
			$itemsContainer.find('.js-products-picker-item').each(function () {
				initItem($(this));
			});
			$itemsContainer.sortable({
				handle: '.cursor-move',
				update: updateOrdering
			});
		};

		this.addProduct = function (productId, productName) {
			var nextIndex = $itemsContainer.find('.js-products-picker-item').size();
			var itemHtml = $productsPicker.data('products-picker-prototype').replace(/__name__/g, nextIndex);
			var $item = $($.parseHTML(itemHtml));
			$item.find('.js-products-picker-item-product-name').text(productName);
			$item.find('.js-products-picker-item-input').val(productId);
			$itemsContainer.append($item);
			initItem($item);
			SS6.formChangeInfo.showInfo();
		};

		this.hasProduct = function (productId) {
			return findProductItemIndex(productId) !== null;
		};

		this.isMainProduct = function (productId) {
			return mainProductId !== '' && mainProductId === productId;
		};

		var initItem = function ($item) {
			productItems.push($item);

			$item.find('.js-products-picker-item-button-delete').click(function () {
				removeItem($item);
			});
		};

		var openProductsPickerWindow = function () {
			$.magnificPopup.open({
				items: {src: $productsPicker.data('products-picker-url').replace('__js_instance_id__', instanceId)},
				type: 'iframe',
				closeOnBgClick: false
			});

			return false;
		};

		var reIndex = function () {
			$itemsContainer.find('.js-products-picker-item-input').each(function (index) {
				var name = $(this).attr("name");
				var newName = name.substr(0, name.lastIndexOf('[') + 1) + index + ']';
				$(this).attr("name", newName);
			});
		};

		var removeItem = function ($item) {
			var productId = $item.find('.js-products-picker-item-input:first').val();
			delete productItems[findProductItemIndex(productId)];
			$item.remove();
			reIndex();
			SS6.formChangeInfo.showInfo();
		};

		var findProductItemIndex = function (productId) {
			for (var key in productItems) {
				if (productItems[key].find('.js-products-picker-item-input:first').val() === productId.toString()) {
					return key;
				}
			}

			return null;
		};

		var updateOrdering = function () {
			reIndex();
			SS6.formChangeInfo.showInfo();
		};
	};

	$(document).ready(function () {
		SS6.productsPicker.init();
	});

})(jQuery);