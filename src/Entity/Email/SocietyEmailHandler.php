<?php

namespace App\Entity\Email;

use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The society email handler contains methods for sending SOCIETY emails.
 */
class SocietyEmailHandler
{
    /**
     * The (main) email handler.
     *
     * @var EmailHandler
     */
    private $emails;

    /**
     * The user handler.
     *
     * @var EmailHandler
     */
    private $users;

    /**
     * The path to the attachments directory.
     *
     * @var string
     */
    private $attachmentsDir;

    /**
     * Constructor function.
     *
     * @param EmailHandler The (main) email handler.
     * @param UserHandler The user handler.
     * @param ParameterBagInterface Symfony's parameter bag interface.
     * @return void
     */
    public function __construct(EmailHandler $emails, UserHandler $users, ParameterBagInterface $params)
    {
        $this->emails = $emails;
        $this->users = $users;
        $this->attachmentsDir = "{$params->get('uploads_directory')}attachments/";
    }

    /**
     * Send an email to all members.
     *
     * @param bool Whether to include current members.
     * @param bool Whether to include lapsed members.
     * @param bool Whether to include members who have declined to recieve general emails.
     * @param Email The email to send.
     * @return Object
     */
    public function sendMembershipEmail(bool $current, bool $lapsed, bool $declining, Email $email): array
    {
        // get the recipients of the email
        $recipientUsers = [];
        if ($current) {
            foreach ($this->users->getMembersInGoodStanding() as $user) {
                if ($declining || $user->getReceiveEmail()) {
                    $recipientUsers[] = $user;
                }
            }
        }
        if ($lapsed) {
            foreach ($this->users->getMembersInArrears() as $user) {
                if ($declining || $user->getReceiveEmail()) {
                    $recipientUsers[] = $user;
                }
            }
        }

        // if there's an attachment, save it
        if ($email->getAttachment()) {
            $email->getAttachment()->move($this->attachmentsDir, $email->getAttachment()->getClientOriginalName());
            $pathToAttachment = $this->attachmentsDir.$email->getAttachment()->getClientOriginalName();
        } else {
            $pathToAttachment = null;
        }

        // send the email to each recipient, keeping track of the results
        $emailsSent = 0;
        $goodRecipients = [];
        $badRecipients = [];
        foreach ($recipientUsers as $user) {
            $emailToSend = clone $email;
            $emailToSend->setRecipient($user);
            $emailToSend->prepareRecipientContent();
            $sent = $this->emails->sendEmail($emailToSend, $pathToAttachment);
            if ($sent) {
                $emailsSent += 1;
                $goodRecipients[] = $user->getEmail();
            } else {
                $badRecipients[] = $user->getEmail();
            }
        }

        // return the results
        return [
            'emailsSent' => $emailsSent,
            'goodRecipients' => $goodRecipients,
            'badRecipients' => $badRecipients
        ];
    }

    /**
     * Send society email from template.
     *
     * @param User The recipient of the email.
     * @param string The label of the template to use.
     * @return void
     */
    public function sendSocietyEmail(User $user, string $label)
    {
        $email = $this->emails->emailFromTemplate($label);
        if ($email) {
            $email->setRecipient($user);
            $email->prepareRecipientContent();
            $this->emails->sendEmail($email);
        }
    }
}
