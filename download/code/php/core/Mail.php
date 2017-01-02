<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2017-01-02
 * Time: 07:41
 */

namespace DownloadCore;


class Mail
{

    protected $mailer;

    /**
     * @var array Email addresses "from"
     */
    protected $from;

    /**
     * @var integer Amount of emails sent the last time
     */
    protected $lastNumSent;


    public function __construct()
    {
        $Settings = new Settings();
        $MailSettings = $Settings->getMail();

        $encryption = $MailSettings['transport']['smtp']['encryption'];
        $host = $MailSettings['transport']['smtp']['host'];
        $port = $MailSettings['transport']['smtp']['port'];
        $username = $MailSettings['transport']['smtp']['username'];
        $password = $MailSettings['transport']['smtp']['password'];

        $this->from = $MailSettings['transport']['smtp']['from'];

        // Create the Transport
        $transport = \Swift_SmtpTransport::newInstance($host, $port)
            ->setUsername($username)
            ->setPassword($password);

        // Create the Mailer using your created Transport
        $this->mailer = \Swift_Mailer::newInstance($transport);

    }

    /**
     * @param $body
     * @param $subject
     * @param array $to
     * @param array $from
     */
    public function createAndSendMessage($body, $subject, array $to, $from = array())
    {

        // Create a message
        $message = $this->createMessage($body, $subject, $to, $from);

        // Send the message
        $this->lastNumSent = $this->sendMessage($message);

    }

    /**
     * @param $body
     * @param $subject
     * @param array $to
     * @param array $from
     * @return \Swift_Mime_MimePart
     */
    protected function createMessage($body, $subject, array $to, array $from)
    {
        $message = \Swift_Message::newInstance(
            (isset($subject) && !empty($subject)) ? $subject : 'No subject'
        )
            ->setFrom(
                (isset($from) && !empty($from)) ? $from : $this->from
            )
            ->setTo($to)
            ->setBody($body);
        return $message;
    }

    /**
     * @param $message
     * @return int
     */
    protected function sendMessage($message)
    {
        $numSent = $this->mailer->send($message);
        return $numSent;
    }

    /**
     * Returns amount of emails sent with the last mail action
     *
     * @return int
     */
    public function getLastNumSent()
    {
        return $this->lastNumSent;
    }


}