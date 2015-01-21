SS6 = SS6 || {};
SS6.number = SS6.number || {};

SS6.number.parseNumber = function (value) {

	var compareValue = value.toString().replace(',', '.');
	var regexpNumber = /^[-+]?[0-9]+((\.|,)?[0-9]+)?$/;
	if (regexpNumber.test(compareValue)) {
		return parseFloat(compareValue);
	} else {
		return null;
	}
}