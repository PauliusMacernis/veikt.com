<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2017-11-29
 * Time: 11:41
 */

namespace DownloadCore\Repeatable;


class RepeatableBag
{
    protected $secondsToSleepBeforeStartMin = 0;
    protected $secondsToSleepBeforeStartMax = 0;
    protected $secondsToSleepBeforeRetryMin = 0;
    protected $secondsToSleepBeforeRetryMax = 0;
    protected $secondsToSleepBeforeActionLog = [];
    protected $retryTimes = 20;

    /**
     * @return int
     */
    public function getSecondsToSleepBeforeStartMin()
    {
        return $this->secondsToSleepBeforeStartMin;
    }

    /**
     * @return int
     */
    public function getSecondsToSleepBeforeStartMax()
    {
        return $this->secondsToSleepBeforeStartMax;
    }

    /**
     * @return int
     */
    public function getSecondsToSleepBeforeRetryMin()
    {
        return $this->secondsToSleepBeforeRetryMin;
    }

    /**
     * @return int
     */
    public function getSecondsToSleepBeforeRetryMax()
    {
        return $this->secondsToSleepBeforeRetryMax;
    }

    /**
     * @return array
     */
    public function getSecondsToSleepBeforeActionLog()
    {
        return $this->secondsToSleepBeforeActionLog;
    }

    public function addToActionLog($secondsValue)
    {
        $this->secondsToSleepBeforeActionLog[] = $secondsValue;
    }

    /**
     * @return int
     */
    public function getRetryTimes()
    {
        return $this->retryTimes;
    }


}