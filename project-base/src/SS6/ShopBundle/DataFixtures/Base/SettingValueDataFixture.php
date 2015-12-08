<?php

namespace SS6\ShopBundle\DataFixtures\Base;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SS6\ShopBundle\Component\DataFixture\AbstractReferenceFixture;
use SS6\ShopBundle\Component\Setting\Setting;
use SS6\ShopBundle\Component\Setting\SettingValue;
use SS6\ShopBundle\Component\String\HashGenerator;
use SS6\ShopBundle\DataFixtures\Base\CurrencyDataFixture;
use SS6\ShopBundle\DataFixtures\Base\PricingGroupDataFixture;
use SS6\ShopBundle\DataFixtures\Base\VatDataFixture;
use SS6\ShopBundle\DataFixtures\Demo\ArticleDataFixture;
use SS6\ShopBundle\Model\Mail\Setting\MailSetting;
use SS6\ShopBundle\Model\Pricing\PricingSetting;
use SS6\ShopBundle\Model\Pricing\Vat\Vat;
use SS6\ShopBundle\Model\Seo\SeoSettingFacade;

class SettingValueDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface {

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function load(ObjectManager $manager) {
		$vat = $this->getReference(VatDataFixture::VAT_HIGH);
		/* @var $vat \SS6\ShopBundle\Model\Pricing\Vat\Vat */
		$pricingGroup1 = $this->getReference(PricingGroupDataFixture::ORDINARY_DOMAIN_1);
		/* @var $pricingGroup2 \SS6\ShopBundle\Model\Pricing\Group\PricingGroup */
		$pricingGroup2 = $this->getReference(PricingGroupDataFixture::ORDINARY_DOMAIN_2);
		/* @var $pricingGroup2 \SS6\ShopBundle\Model\Pricing\Group\PricingGroup */
		$defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
		/* @var $defaultCurrency \SS6\ShopBundle\Model\Pricing\Currency\Currency */
		$domain2DefaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);
		/* @var $defaultCurrency \SS6\ShopBundle\Model\Pricing\Currency\Currency */
		$defaultInStockAvailability = $this->getReference(AvailabilityDataFixture::IN_STOCK);
		/* @var $defaultInStockAvailability \SS6\ShopBundle\Model\Product\Availability\Availability */
		$termsAndConditions = $this->getReference(ArticleDataFixture::TERMS_AND_CONDITIONS_1);
		/* @var $termsAndConditions \SS6\ShopBundle\Model\Article\Article */
		$termsAndConditionsDomain2 = $this->getReference(ArticleDataFixture::TERMS_AND_CONDITIONS_2);
		/* @var $termsAndConditionsDomain2 \SS6\ShopBundle\Model\Article\Article */
		$hashGenerator = $this->get(HashGenerator::class);
		/* @var $hashGenerator \SS6\ShopBundle\Component\String\HashGenerator */

		$orderSentTextCs = '
			<p>
				Objednávka číslo {number} byla odeslána, děkujeme za Váš nákup.
				Budeme Vás kontaktovat o dalším průběhu vyřizování. <br /><br />
				Uschovejte si permanentní <a href="{order_detail_url}">odkaz na detail objednávky</a>. <br />
				{transport_instructions} <br />
				{payment_instructions} <br />
			</p>
		';
		$orderSentTextEn = '
			<p>
				Order number {number} has been sent, thank you for your purchase.
				We will contact you about next order status. <br /><br />
				<a href="{order_detail_url}">Track</a> the status of your order. <br />
				{transport_instructions} <br />
				{payment_instructions} <br />
			</p>
		';

		// @codingStandardsIgnoreStart
		$manager->persist(new SettingValue(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT, SettingValue::DOMAIN_ID_COMMON));
		$manager->persist(new SettingValue(PricingSetting::ROUNDING_TYPE, PricingSetting::ROUNDING_TYPE_INTEGER, SettingValue::DOMAIN_ID_COMMON));
		$manager->persist(new SettingValue(Vat::SETTING_DEFAULT_VAT, $vat->getId(), SettingValue::DOMAIN_ID_COMMON));
		$manager->persist(new SettingValue(Setting::ORDER_SUBMITTED_SETTING_NAME, $orderSentTextCs, 1));
		$manager->persist(new SettingValue(Setting::ORDER_SUBMITTED_SETTING_NAME, $orderSentTextEn, 2));
		$manager->persist(new SettingValue(MailSetting::MAIN_ADMIN_MAIL, 'no-reply@netdevelo.cz', 1));
		$manager->persist(new SettingValue(MailSetting::MAIN_ADMIN_MAIL_NAME, 'Shopsys', 1));
		$manager->persist(new SettingValue(MailSetting::MAIN_ADMIN_MAIL, 'no-reply@netdevelo.cz', 2));
		$manager->persist(new SettingValue(MailSetting::MAIN_ADMIN_MAIL_NAME, '2.Shopsys', 2));
		$manager->persist(new SettingValue(Setting::DEFAULT_PRICING_GROUP, $pricingGroup1->getId(), 1));
		$manager->persist(new SettingValue(Setting::DEFAULT_PRICING_GROUP, $pricingGroup2->getId(), 2));
		$manager->persist(new SettingValue(PricingSetting::DEFAULT_CURRENCY, $defaultCurrency->getId(), SettingValue::DOMAIN_ID_COMMON));
		$manager->persist(new SettingValue(PricingSetting::DEFAULT_DOMAIN_CURRENCY, $defaultCurrency->getId(), 1));
		$manager->persist(new SettingValue(PricingSetting::DEFAULT_DOMAIN_CURRENCY, $domain2DefaultCurrency->getId(), 2));
		$manager->persist(new SettingValue(Setting::DEFAULT_AVAILABILITY_IN_STOCK, $defaultInStockAvailability->getId(), SettingValue::DOMAIN_ID_COMMON));
		$manager->persist(new SettingValue(PricingSetting::FREE_TRANSPORT_AND_PAYMENT_PRICE_LIMIT, null, 1));
		$manager->persist(new SettingValue(PricingSetting::FREE_TRANSPORT_AND_PAYMENT_PRICE_LIMIT, null, 2));
		$manager->persist(new SettingValue(SeoSettingFacade::SEO_META_DESCRIPTION_MAIN_PAGE, 'ShopSys 6 - nejlepší řešení pro váš internetový obchod.', 1));
		$manager->persist(new SettingValue(SeoSettingFacade::SEO_META_DESCRIPTION_MAIN_PAGE, 'ShopSys 6 - the best solution for your eshop.', 2));
		$manager->persist(new SettingValue(SeoSettingFacade::SEO_TITLE_MAIN_PAGE, 'ShopSys 6 - Titulní strana', 1));
		$manager->persist(new SettingValue(SeoSettingFacade::SEO_TITLE_MAIN_PAGE, 'ShopSys 6 - Title page', 2));
		$manager->persist(new SettingValue(SeoSettingFacade::SEO_TITLE_ADD_ON, ' | Demo obchod', 1));
		$manager->persist(new SettingValue(SeoSettingFacade::SEO_TITLE_ADD_ON, ' | Demo eshop', 2));
		$manager->persist(new SettingValue(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $termsAndConditions->getId(), 1));
		$manager->persist(new SettingValue(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $termsAndConditionsDomain2->getId(), 2));
		$manager->persist(new SettingValue(Setting::DOMAIN_DATA_CREATED, true, 1));
		$manager->persist(new SettingValue(Setting::DOMAIN_DATA_CREATED, true, 2));
		$manager->persist(new SettingValue(Setting::FEED_HASH, $hashGenerator->generateHash(10), SettingValue::DOMAIN_ID_COMMON));
		// @codingStandardsIgnoreStop

		$manager->flush();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDependencies() {
		return [
			ArticleDataFixture::class,
			AvailabilityDataFixture::class,
			VatDataFixture::class,
		];
	}

}
