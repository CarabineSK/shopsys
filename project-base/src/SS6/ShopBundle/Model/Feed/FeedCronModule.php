<?php

namespace SS6\ShopBundle\Model\Feed;

use SS6\ShopBundle\Component\Cron\IteratedCronModuleInterface;
use SS6\ShopBundle\Component\Setting\Setting;
use SS6\ShopBundle\Component\Setting\SettingValue;
use SS6\ShopBundle\Model\Feed\FeedFacade;
use Symfony\Bridge\Monolog\Logger;

class FeedCronModule implements IteratedCronModuleInterface {

	/**
	 * @var \SS6\ShopBundle\Model\Feed\FeedFacade
	 */
	private $feedFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Feed\FeedGenerationConfig|null
	 */
	private $feedGenerationConfigToContinue;

	/**
	 * @var \SS6\ShopBundle\Component\Setting\Setting
	 */
	private $setting;

	public function __construct(FeedFacade $feedFacade, Setting $setting) {
		$this->feedFacade = $feedFacade;
		$this->setting = $setting;
	}

	/**
	 * @inheritdoc
	 */
	public function setLogger(Logger $logger) {

	}

	/**
	 * @inheritdoc
	 */
	public function iterate() {
		if ($this->feedGenerationConfigToContinue === null) {
			$this->feedGenerationConfigToContinue = $this->feedFacade->getFirstFeedGenerationConfig();
		}
		$this->feedGenerationConfigToContinue = $this->feedFacade->generateFeedsIteratively($this->feedGenerationConfigToContinue);

		return $this->feedGenerationConfigToContinue !== null;
	}

	/**
	 * @inheritdoc
	 */
	public function sleep() {
		$this->setting->set(
			Setting::FEED_NAME_TO_CONTINUE,
			$this->feedGenerationConfigToContinue->getFeedName(),
			SettingValue::DOMAIN_ID_COMMON
		);
		$this->setting->set(
			Setting::FEED_DOMAIN_ID_TO_CONTINUE,
			$this->feedGenerationConfigToContinue->getDomainId(),
			SettingValue::DOMAIN_ID_COMMON
		);
		$this->setting->set(
			Setting::FEED_ITEM_ID_TO_CONTINUE,
			$this->feedGenerationConfigToContinue->getFeedItemId(),
			SettingValue::DOMAIN_ID_COMMON
		);
	}

	/**
	 * @inheritdoc
	 */
	public function wakeUp() {
		$this->feedGenerationConfigToContinue = new FeedGenerationConfig(
			$this->setting->get(Setting::FEED_NAME_TO_CONTINUE),
			$this->setting->get(Setting::FEED_DOMAIN_ID_TO_CONTINUE),
			$this->setting->get(Setting::FEED_ITEM_ID_TO_CONTINUE)
		);
	}

}
