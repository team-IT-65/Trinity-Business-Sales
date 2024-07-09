<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events\Subscribers;

use DateInterval;
use DateTime;
use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Events\Contracts\EventContract;
use GoDaddy\WordPress\MWC\Common\Events\Contracts\SubscriberContract;
use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Schedule\Exceptions\InvalidScheduleException;
use GoDaddy\WordPress\MWC\Common\Schedule\Schedule;
use GoDaddy\WordPress\MWC\Common\Schedule\Types\SingleAction;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events\CareUserLogInEvent;

/**
 * Subscriber to the {@see CareUserLogInEvent} event.
 *
 * This subscriber will schedule a delayed deletion of the Care Agent user.
 */
class ScheduleCareAgentDeleteSubscriber implements SubscriberContract
{
    /** @var string scheduled action hook name for the care user deletion */
    public const USER_DELETE_ACTION_NAME = 'mwc_delete_user';

    /**
     * Gets the date when the care user should be deleted.
     *
     * @return DateTime
     */
    protected function getDeletionDate() : DateTime
    {
        try {
            $interval = TypeHelper::string(Configuration::get('features.wordpress_sso.care.autoDeleteInterval'), '');

            return (new DateTime())->add(new DateInterval($interval));
        } catch (Exception $exception) {
            SentryException::getNewInstance('Care user deletion interval configuration is invalid, using a 24 hour interval as a fallback.', $exception);

            return (new DateTime())->add(new DateInterval('PT24H'));
        }
    }

    /**
     * Handles the event.
     *
     * @param EventContract|CareUserLogInEvent $event
     * @return void
     */
    public function handle(EventContract $event)
    {
        if (! $event instanceof CareUserLogInEvent) {
            return;
        }

        $userId = $event->getUser()->getId();

        if (! $userId) {
            $this->issueSentryException(sprintf('Could not get a valid user ID for the %s event.', get_class($event)), null);

            return;
        }

        $action = $this->getScheduleDeleteActionForUser($userId);

        $this->maybeUnscheduleExistingAction($action);

        try {
            $action->schedule();
        } catch (InvalidScheduleException $exception) {
            $this->issueSentryException(null, $exception);
        }
    }

    /**
     * Gets the {@see SingleAction} to schedule for deleting the care agent user with given ID.
     *
     * @param int $userId
     * @return SingleAction
     */
    protected function getScheduleDeleteActionForUser(int $userId) : SingleAction
    {
        return Schedule::singleAction()
            ->setScheduleAt($this->getDeletionDate())
            ->setName(ScheduleCareAgentDeleteSubscriber::USER_DELETE_ACTION_NAME)
            ->setArguments($userId);
    }

    /**
     * Unschedule an existing deletion action if already scheduled.
     *
     * @param SingleAction $action
     * @return void
     */
    protected function maybeUnscheduleExistingAction(SingleAction $action)
    {
        try {
            if ($action->isScheduled()) {
                $action->unschedule();
            }
        } catch (InvalidScheduleException $exception) {
            $this->issueSentryException(null, $exception);
        }
    }

    /**
     * Triggers a {@see SentryException} with the given message and previous exception, if applicable.
     *
     * @param string|null $message will use a default message when not provided
     * @param Exception|null $exception
     * @return void
     */
    protected function issueSentryException(?string $message, ?Exception $exception) : void
    {
        SentryException::getNewInstance($message ?: "Failed to schedule the care user's delete action", $exception);
    }
}
