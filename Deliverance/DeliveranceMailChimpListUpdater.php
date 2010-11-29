<?php

require_once 'Deliverance/DeliveranceListUpdater.php';
require_once 'Deliverance/DeliveranceMailChimpList.php';

/**
 * MailChimp specific application to update mailing list with new and queued
 * subscriber requests.
 *
 * @package   Deliverance
 * @copyright 2009-2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class DeliveranceMailChimpListUpdater extends DeliveranceListUpdater
{
	// {{{ protected function getList()

	protected function getList()
	{
		// long custom timeout
		return new DeliveranceMailChimpList($this, null, 90000);
	}

	// }}}
	// {{{ protected function handleResult()

	protected function handleResult($result, $success_message)
	{
		$clear_queued = parent::handleResult($result, $success_message);

		if (is_array($result)) {
			$clear_queued = true;

			$this->debug(sprintf($success_message,
				$result['success_count']));

			if ($result['error_count']) {
				$errors = array();
				$not_found_count = 0;
				$bounced_count = 0;
				$previously_unsubscribed_count = 0;
				$invalid_count = 0;
				$queued_count = 0;

				// don't throw errors for codes we know can be ignored.
				foreach ($result['errors'] as $error) {
					switch ($error['code']) {
					case DeliveranceMailChimpList::NOT_FOUND_ERROR_CODE:
					case DeliveranceMailChimpList::NOT_SUBSCRIBED_ERROR_CODE:
						$not_found_count++;
						break;

					case DeliveranceMailChimpList::PREVIOUSLY_UNSUBSCRIBED_ERROR_CODE:
						$previously_unsubscribed_count++;
						break;

					case DeliveranceMailChimpList::BOUNCED_ERROR_CODE:
						$bounced_count++;
						break;

					case DeliveranceMailChimpList::INVALID_ADDRESS_ERROR_CODE:
						$invalid_count++;
						break;

					case DeliveranceList::QUEUED:
						$queued_count++;
						break;

					default:
						$error_message = sprintf(
							Deliverance::_('code: %s - message: %s.'),
							$error['code'],
							$error['message']);

						$errors[]  = $error_message;
						$execption = new SiteException($error_message);
						// don't exit on returned errors
						$execption->process(false);
					}
				}

				if ($not_found_count > 0) {
					$this->debug(
						sprintf(
							Deliverance::_('%s addresses not found.')."\n",
							$not_found_count
						)
					);
				}

				if ($previously_unsubscribed_count > 0) {
					$this->debug(
						sprintf(
							Deliverance::_(
								'%s addresses have previously subscribed, '.
								'and cannot be resubscribed.'
							)."\n",
							$previously_unsubscribed_count
						)
					);
				}

				if ($bounced_count > 0) {
					$this->debug(
						sprintf(
							Deliverance::_(
								'%s addresses have bounced, and cannot be '.
								'resubscribed.'
							)."\n",
							$bounced_count
						)
					);
				}

				if ($invalid_count > 0) {
					$this->debug(
						sprintf(
							Deliverance::_('%s invalid addresses.')."\n",
							$invalid_count
						)
					);
				}

				if ($queued_count > 0) {
					$clear_queued = false;
					$this->debug(
						sprintf(
							Deliverance::_('%s addresses queued.')."\n",
							$queued_count
						)
					);
				}

				if (count($errors)) {
					$this->debug(
						sprintf(
							Deliverance::_('%s errors:')."\n",
							count($errors)
						)
					);

					foreach ($errors as $error) {
						$this->debug($error."\n");
					}
				}
			}
		}

		return $clear_queued;
	}

	// }}}
}

?>