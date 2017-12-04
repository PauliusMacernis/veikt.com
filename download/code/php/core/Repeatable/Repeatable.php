<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2017-11-29
 * Time: 08:33
 */

namespace DownloadCore\Repeatable;


trait Repeatable
{
    /**
     * @var RepeatableBag $repeatableBag Some statistics/info/settings/etc. on the repeats performed
     */
    protected $repeatableBag;

    /**
     * @var array $repeatableBagClientConfig Mainly proxy settings and other info used for the Browser client in action
     */
    protected $repeatableBagClientConfig = [];

    /**
     * @return array
     */
    public function getRepeatableBagClientConfig()
    {
        return $this->repeatableBagClientConfig;
    }

    protected function setRepeatableBagClientConfig(array $clientConfig)
    {
        $this->repeatableBagClientConfig = $clientConfig;
    }

    /**
     * Calls $this->$methodName($argN) method.
     * Repeats the call
     *  for $retryTimes (RepeatableBag) times
     *  if $this->$methodName returns NULL or FALSE
     * Every first and other call sleeps for a random amount of seconds between
     *  $secondsToSleepBeforeStartMin (RepeatableBag) and $secondsToSleepBeforeRetryMin (RepeatableBag)
     *  and
     *  $secondsToSleepBeforeStartMax (RepeatableBag) and $secondsToSleepBeforeRetryMax (RepeatableBag)
     *
     * @todo: This one definitely requires the test to be written
     *
     * @param string $methodName Name of method in $this class
     * @param mixed $arg1 Any type of argument passed to $this->$methodName
     * @return mixed|null           Returned value of $this->$methodName method
     *
     */
    protected function doRepeatableAction($methodName)
    {
        $repeatableBag = new RepeatableBag();

        if (!method_exists($this, $methodName)) {
            return null;
        }

        // Wait before starting
        $secondsToSleepBeforeActionGot = rand(
            $repeatableBag->getSecondsToSleepBeforeStartMin(), $repeatableBag->getSecondsToSleepBeforeStartMax()
        );
        $repeatableBag->addToActionLog($secondsToSleepBeforeActionGot);
        sleep($secondsToSleepBeforeActionGot);

        $retryTimes = $repeatableBag->getRetryTimes();
        if ((int)$retryTimes < 0) { // be secured!
            return null;
        }

        // Get arguments passed
        $args = func_get_args();
        // Skip $methodName
        array_shift($args);

        // Do action
        while ($retryTimes) {

            if ($repeatableBag->getRetryTimes() != $retryTimes) { // Not the first time?
                $secondsToSleepBeforeActionGot = rand(
                    $repeatableBag->getSecondsToSleepBeforeRetryMin(), $repeatableBag->getSecondsToSleepBeforeRetryMax()
                );
                $repeatableBag->addToActionLog($secondsToSleepBeforeActionGot);
                //var_dump('Sleeping for ' . $secondsToSleepBeforeActionGot);
                sleep($secondsToSleepBeforeActionGot); // Wait
            }

            //var_dump('CALLING "' . $methodName . '"". Try #' . ($this::RETRY_TIMES - $retryTimes +1));
            $this->setRepeatableBag($repeatableBag);
            //var_dump($methodName, $args); //die();
            $result = call_user_func_array(array($this, $methodName), $args);

            //var_dump('Result: ');
            //var_dump($result);

            // If result is strictly NULL or FALSE then it means "NO SUCCESS"
            if (isset($result) && ($result !== false)) {
                return $result;
            }

            $retryTimes--;
        }

        // Result may be FALSE or NULL
        // If result is FALSE...
        if (isset($result)) {
            return $result;
        }

        // If result is NULL or any surprises, just in case...
        return null;

    }

    protected function getRepeatableBag()
    {
        return $this->repeatableBag;
    }

    protected function setRepeatableBag(RepeatableBag $info)
    {
        $this->repeatableBag = $info;
    }


}