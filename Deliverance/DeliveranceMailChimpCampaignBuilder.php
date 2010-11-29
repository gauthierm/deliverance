<?php

require_once 'Deliverance/DeliveranceCampaignBuilder.php';
require_once 'Deliverance/DeliveranceMailChimpList.php';

/**
 * Builds campaigns from provided shortnames, and sets them up on MailChimp
 *
 * @package   Deliverance
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class DeliveranceMailChimpCampaignBuilder extends
	DeliveranceCampaignBuilder
{
	// {{{ protected properties

	/**
	 * @var boolean
	 */
	protected $timewarp = true;

	// }}}
	// {{{ public funtcion __construct()

	public function __construct($id, $filename, $title, $documentation)
	{
		parent::__construct($id, $filename, $title, $documentation);

		$timewarp = new SiteCommandLineArgument(array('--no-timewarp'),
			'setNoTimewarp', 'Tells the builder to turn timewarp off.');

		$this->addCommandLineArgument($timewarp);
	}

	// }}}
	// {{{ public function setNoTimewarp()

	public function setNoTimewarp()
	{
		$this->timewarp = false;
	}

	// }}}
	// {{{ protected function getList()

	protected function getList()
	{
		return new DeliveranceMailChimpList($this, null, 90000);
	}

	// }}}
	// {{{ protected function displayFinalOutput()

	protected function displayFinalOutput()
	{
		$browse_link = sprintf(
			$this->config->mail_chimp->preview_url,
			$this->config->mail_chimp->user_id,
			$this->campaign->id);

		$this->debug(sprintf(
			"\nView the generated campaign at:\n%s\n\n", $browse_link));
	}

	// }}}
}

?>