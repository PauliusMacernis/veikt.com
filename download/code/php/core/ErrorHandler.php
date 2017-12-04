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

        $this->sendErrorEmail(
            print_r($data, true),
            'Veikt error. Case: defaultErrorHandler'
        );

    }

    public function sendErrorEmail($body, $subject = 'Veikt error.')
    {

        $to = $this->getTo();

        $Mail = new Mail();
        $Mail->createAndSendMessage($body, $subject, $to);

        printf("Sent %d email messages\n", $Mail->getLastNumSent());

    }

    /**
     * @return mixed
     */
    protected function getTo($toKey = 'to-on-error')
    {
        $Settings = new Settings();
        $MailSettings = $Settings->getMail();
        $to = $MailSettings['transport']['smtp'][$toKey];
        return $to;
    }

    public function defaultRegisterShutdown()
    {
        $lastError = error_get_last();
        if (null === $lastError) {
            return;
        }

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

        $this->sendErrorEmail(
            print_r([
                'LAST_ERROR' => print_r($lastError, true)
            ], true),
            'Veikt error. Case: defaultRegisterShutdown');

    }

}