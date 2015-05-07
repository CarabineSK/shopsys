(function ($){
	$(document).ready(function () {
		var $advertForm = $('form[name="advert"]');

		$.fn.initAdvertType = function(){
			var $checkedType = $(this).find('input[name="advert[type]"]:checked').val();
			$(this).find('.js-advert-type-content').hide();
			$(this).find('.js-advert-type-content[data-type=' + $checkedType + ']').show();
		};

		$advertForm.initAdvertType();
		$advertForm.find('input[name="advert[type]"]').on('change',function(){
			$advertForm.initAdvertType();
		});

		$advertForm.jsFormValidator({
			'groups': function () {
				var groups = [SS6.constant('\\SS6\\ShopBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];

				if ($('input[name="advert[type]"]:checked').val() === SS6.constant('\\SS6\\ShopBundle\\Model\\Advert\\Advert::TYPE_CODE')) {
					groups.push(SS6.constant('\\SS6\\ShopBundle\\Form\\Admin\\Advert\\AdvertFormType::VALIDATION_GROUP_TYPE_CODE'));
				} else if ($('input[name="advert[type]"]:checked').val() === SS6.constant('\\SS6\\ShopBundle\\Model\\Advert\\Advert::TYPE_IMAGE')) {
					groups.push(SS6.constant('\\SS6\\ShopBundle\\Form\\Admin\\Advert\\AdvertFormType::VALIDATION_GROUP_TYPE_IMAGE'));
				}
				return groups;
			}
		});
	});
})(jQuery);
