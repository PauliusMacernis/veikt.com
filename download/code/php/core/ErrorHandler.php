<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-12-28
 * Time: 22:00
 */

namespace DownloadCore;


class ErrorHandler extends \Exception
{
    public function defaultErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {

        $data = [
            'ERROR_GET_LAST' => error_get_last(),
            'FUNC_GET_ARGS' => func_get_args()
        ];

        $this->sendErrorEmail(print_r($data, true));

    }

    public function sendErrorEmail($bodyAsString)
    {

        $Settings = new Settings();
        $MailSettings = $Settings->getMail();

        $encryption = $MailSettings['transport']['smtp']['encryption'];
        $host = $MailSettings['transport']['smtp']['host'];
        $port = $MailSettings['transport']['smtp']['port'];
        $username = $MailSettings['transport']['smtp']['username'];
        $password = $MailSettings['transport']['smtp']['password'];

        $from = $MailSettings['transport']['smtp']['from'];
        $to = $MailSettings['transport']['smtp']['to-error'];

        // Create the Transport
        $transport = \Swift_SmtpTransport::newInstance($host, $port)
            ->setUsername($username)
            ->setPassword($password);

        // Create the Mailer using your created Transport
        $mailer = \Swift_Mailer::newInstance($transport);

        // Create a message
        $message = \Swift_Message::newInstance('Veikt error. ')
            ->setFrom($from)
            ->setTo($to)
            ->setBody($bodyAsString);

        // Send the message
        $numSent = $mailer->send($message);
        printf("Sent %d email messages\n", $numSent);

    }

    public function defaultRegisterShutdown()
    {
        // @TODO: Change the functionality of mailing.
        //  It would be better if all errors would be filtered to unique only
        //  and then saved to file or somewhere else into one place.
        //  and only sent when the "main.sh" script (either download,
        //  normalize, publicize, ...) has the job completely finished.
        // At the moment register_shutdown_function is disabled in normalize and
        //  publicize, because if there is one error in the code of normalization
        //  then thousands of emails with the same content will be sent...
        //  We do not need thousands... Need to fix that.
        //  Enable register_shutdown_function in the entire project
        //  after mailing functionality is fixed.

        $this->sendErrorEmail(print_r(error_get_last(), true));

    }

}