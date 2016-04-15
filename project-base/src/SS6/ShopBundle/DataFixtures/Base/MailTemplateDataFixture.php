<?php

namespace SS6\ShopBundle\DataFixtures\Base;

use Doctrine\Common\Persistence\ObjectManager;
use SS6\ShopBundle\Component\DataFixture\AbstractReferenceFixture;
use SS6\ShopBundle\Model\Mail\MailTemplate;
use SS6\ShopBundle\Model\Mail\MailTemplateData;

class MailTemplateDataFixture extends AbstractReferenceFixture {

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function load(ObjectManager $manager) {
		$mailTemplateData = new MailTemplateData();
		$mailTemplateData->sendMail = true;

		$mailTemplateData->subject = 'Děkujeme za objednávku č. {number} ze dne {date}';
		$mailTemplateData->body = 'Dobrý den,<br /><br />'
			. 'Vaše objednávka byla úspěšně vytvořena.<br /><br />'
			. 'O dalších stavech objednávky Vás budeme informovat.<br />'
			. 'Čislo objednávky: {number} <br />'
			. 'Datum a čas vytvoření: {date} <br />'
			. 'URL adresa eshopu: {url} <br />'
			. 'URL adresa na detail objednávky: {order_detail_url} <br />'
			. 'Doprava: {transport} <br />'
			. 'Platba: {payment} <br />'
			. 'Celková cena s DPH: {total_price} <br />'
			. 'Fakturační adresa:<br /> {billing_address} <br />'
			. 'Doručovací adresa: {delivery_address} <br />'
			. 'Poznámka: {note} <br />'
			. 'Produkty: {products} <br />'
			. '{transport_instructions} <br />'
			. '{payment_instructions}';

		$this->createMailTemplate($manager, 'order_status_1', 1, $mailTemplateData);

		$mailTemplateData->subject = 'Změna stavu vaší objednávky';
		$mailTemplateData->body = 'Dobrý den, <br /><br />'
			. 'Vaši objednávku již vyřizujeme.';

		$this->createMailTemplate($manager, 'order_status_2', 1, $mailTemplateData);

		$mailTemplateData->subject = 'Změna stavu vaší objednávky';
		$mailTemplateData->body = 'Dobrý den, <br /><br />'
			. 'Vaše objednávka je vyřízena.';

		$this->createMailTemplate($manager, 'order_status_3', 1, $mailTemplateData);

		$mailTemplateData->subject = 'Změna stavu vaší objednávky';
		$mailTemplateData->body = 'Dobrý den, <br /><br />'
			. 'Vaše objednávka byla stornována.';

		$this->createMailTemplate($manager, 'order_status_4', 1, $mailTemplateData);

		$mailTemplateData->subject = 'Žádost o nové heslo';
		$mailTemplateData->body = 'Dobrý den.<br /><br />'
			. 'Nové heslo nastavíte zde: <a href="{new_password_url}">{new_password_url}</a>';

		$this->createMailTemplate($manager, MailTemplate::RESET_PASSWORD_NAME, 1, $mailTemplateData);

		$mailTemplateData->subject = 'Potvrzení registrace';
		$mailTemplateData->body = 'Dobrý den, <br /><br />'
			. 'potvrzujeme Vaši registraci v eshopu. <br />'
			. 'Jméno: {first_name} {last_name}<br />'
			. 'Email: {email}<br />'
			. 'URL adresa eshopu: {url}<br />'
			. 'Přihlašovací stránka: {login_page}';

		$this->createMailTemplate($manager, MailTemplate::REGISTRATION_CONFIRM_NAME, 1, $mailTemplateData);

		$mailTemplateData->subject = 'Děkujeme za objednávku na druhé doméně';
		$mailTemplateData->body = 'Dobrý den,<br /><br />'
			. 'Vaše objednávka byla úspěšně vytvořena.<br /><br />'
			. 'O dalších stavech objednávky Vás budeme informovat.<br />'
			. 'Čislo objednávky: {number} <br />'
			. 'Datum a čas vytvoření: {date} <br />'
			. 'URL adresa eshopu: {url} <br />'
			. 'URL adresa na detail objednávky: {order_detail_url} <br />'
			. 'Doprava: {transport} <br />'
			. 'Platba: {payment} <br />'
			. 'Celková cena s DPH: {total_price} <br />'
			. 'Fakturační adresa:<br /> {billing_address} <br />'
			. 'Doručovací adresa: {delivery_address} <br />'
			. 'Poznámka: {note} <br />'
			. 'Produkty: {products} <br />'
			. '{transport_instructions} <br />'
			. '{payment_instructions}';

		$this->createMailTemplate($manager, 'order_status_1', 2, $mailTemplateData);

		$mailTemplateData->subject = 'Změna stavu vaší objednávky na druhé doméně';
		$mailTemplateData->body = 'Dobrý den, <br /><br />'
			. 'Vaši objednávku již vyřizujeme.';

		$this->createMailTemplate($manager, 'order_status_2', 2, $mailTemplateData);

		$mailTemplateData->subject = 'Změna stavu vaší objednávky na druhé doméně';
		$mailTemplateData->body = 'Dobrý den, <br /><br />'
			. 'Vaše objednávka je vyřízena.';

		$this->createMailTemplate($manager, 'order_status_3', 2, $mailTemplateData);

		$mailTemplateData->subject = 'Změna stavu vaší objednávky na druhé doméně';
		$mailTemplateData->body = 'Dobrý den, <br /><br />'
			. 'Vaše objednávka byla stornována.';

		$this->createMailTemplate($manager, 'order_status_4', 2, $mailTemplateData);

		$mailTemplateData->subject = 'Potvrzení registrace na druhé doméně';
		$mailTemplateData->body = 'Dobrý den, <br /><br />'
			. 'potvrzujeme Vaši registraci v eshopu.<br />'
			. 'Jméno: {first_name} {last_name}<br />'
			. 'Email: {email}<br />'
			. 'URL adresa eshopu: {url}<br />'
			. 'Přihlašovací stránka: {login_page}';

		$this->createMailTemplate($manager, MailTemplate::REGISTRATION_CONFIRM_NAME, 2, $mailTemplateData);

		$mailTemplateData->subject = 'Žádost o nové heslo';
		$mailTemplateData->body = 'Dobrý den.<br /><br />'
			. 'Nové heslo nastavíte zde: <a href="{new_password_url}">{new_password_url}</a>';

		$this->createMailTemplate($manager, MailTemplate::RESET_PASSWORD_NAME, 2, $mailTemplateData);
	}

	/**
	 * @param string $name
	 * @param int $domainId
	 * @param \SS6\ShopBundle\Model\Mail\MailTemplateData $mailTemplateData
	 */
	private function createMailTemplate(
		ObjectManager $manager,
		$name,
		$domainId,
		MailTemplateData $mailTemplateData
	) {
		$mailTemplate = new MailTemplate($name, $domainId, $mailTemplateData);
		$manager->persist($mailTemplate);
		$manager->flush($mailTemplate);
	}
}
