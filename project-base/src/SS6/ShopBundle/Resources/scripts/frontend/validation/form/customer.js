(function ($){
	$(document).ready(function () {
		var $customerDeliveryAddressForm = $('#customer_deliveryAddressData');
		$customerDeliveryAddressForm.jsFormValidator({
			'groups': function () {

				var groups = [SS6.constant('\\SS6\\ShopBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
				if ($customerDeliveryAddressForm.find('#customer_deliveryAddressData_addressFilled').is(':checked')) {
					groups.push(SS6.constant('\\SS6\\ShopBundle\\Form\\Front\\Customer\\DeliveryAddressFormType::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS'));
				}

				return groups;
			}
		});
		var $customerBillingAddressForm = $('#customer_billingAddressData');
		$customerBillingAddressForm.jsFormValidator({
			'groups': function () {

				var groups = [SS6.constant('\\SS6\\ShopBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
				if ($customerBillingAddressForm.find('#customer_billingAddressData_companyCustomer').is(':checked')) {
					groups.push(SS6.constant('\\SS6\\ShopBundle\\Form\\Front\\Customer\\BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER'));
				}

				return groups;
			}
		});

	});
})(jQuery);
