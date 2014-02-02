<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ScheduledTime;

use Exception;
use Piwik\ScheduledTime;

/**
 * Weekly class is used to schedule tasks every week.
 *
 * @see ScheduledTask
 *
 */
class Weekly extends ScheduledTime
{
    /**
     * @see ScheduledTime::getRescheduledTime
     * @return int
     */
    public function getRescheduledTime()
    {
        $currentTime = $this->getTime();

        $daysFromNow = 7;

        // Adjusts the scheduled day
        if ($this->day !== null) {
            $daysFromNow = ($this->day - date('N', $currentTime) + 7) % 7;

            if ($daysFromNow == 0) {
                $daysFromNow = 7;
            }
        }

        // Adds correct number of days
        $rescheduledTime = mktime(date('H', $currentTime),
            date('i', $currentTime),
            date('s', $currentTime),
            date('n', $currentTime),
            date('j', $currentTime) + $daysFromNow,
            date('Y', $currentTime)
        );
 
        // Adjusts the scheduled hour
        $rescheduledTime = $this->adjustHour($rescheduledTime);

        return $rescheduledTime;
    }

    /**
     * @param int $day the day to set, has to be >= 1 and < 8
     * @throws Exception if parameter _day is invalid
     */
    public function setDay($day)
    {
        if (!is_int($day)) {
            $day = self::getDayIntFromString($day);
        }

        if (!($day >= 1 && $day < 8)) {
            throw new Exception ("Invalid day parameter, must be >=1 and < 8");
        }

        $this->day = $day;
    }

    public static function getDayIntFromString($dayString)
    {
        $time = strtotime($dayString);
        if ($time === false) {
            throw new Exception("Invalid day string '$dayString'. Must be 'monday', 'tuesday', etc.");
        }

        return date("N", $time);
    }
}
