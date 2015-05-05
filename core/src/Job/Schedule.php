<?php
namespace SporkTools\Core\Job;

class Schedule implements \IteratorAggregate
{
    protected $times = array();
    
    public function __construct($times = null)
    {
        if (null != $times) {
            foreach (explode(',', $times) as $time) {
                $this->times[] = explode(':', $time);
            }
        }
    }
    
    public function __toString()
    {
        $times = array();
        foreach ($this->times as $time) {
            $times[] = implode(':', $time);
        }
        return implode(',', $times);
    }
    
    public function addTime($interval, $offset)
    {
        $this->times[] = array($interval, $offset);
    }
    
    public function getIterator()
    {
        return new \ArrayIterator($this->times);
    }
    
    public function getDescription()
    {
        $descriptions = array();
        foreach ($this->times as $time) {
            $descriptions[] = $this->timeToDescription($time[0], $time[1]);
        }
        return implode(', ', $descriptions);
    }
    
    public function getLast()
    {
        if (empty($this->times)) {
            return null;
        }
        $last = null;
        foreach ($this->times as $time) {
            if ($time[0] == 2628000 /*monthly*/) {
                $now = getdate();
                $day = floor($time[1] / 86400) + 1;
                if ($now['mday'] > $day) {
                    $month = $now['mon'];
                    $year = $now['year'];
                } else {
                    if ($now['mon'] == 1) {
                        $month = 12;
                        $year = $now['year'] - 1;
                    } else {
                        $month = $now['mon'] - 1;
                        $year = $now['year'];
                    }
                }
                $datetime = new \DateTime('@' . $time[1]);
                $datetime->setDate($year, $month, $day);
            } else {
                $timestamp = $time[1] + floor((time() - $time[1]) / $time[0]) * $time[0];
                $datetime = new \DateTime('@' . $timestamp);
            }
            if (null === $last || $datetime > $last) {
                $last = $datetime;
            }
        }
        $last->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        return $last;
    }
    
    public function getNext()
    {
        if (empty($this->times)) {
            return null;
        }
        $next = null;
        foreach ($this->times as $time) {
            if ($time[0] == 2628000 /*monthly*/) {
                $now = getdate();
                $day = floor($time[1] / 86400) + 1;
                if ($now['mday'] > $day) {
                    if ($now['mon'] == 12) {
                        $month = 1;
                        $year = $now['year'] + 1;
                    } else {
                        $month = $now['mon'] + 1;
                        $year = $now['year'];
                    }
                } else {
                    $month = $now['mon'];
                    $year = $now['year'];
                }
                $datetime = new \DateTime('@' . $time[1]);
                $datetime->setDate($year, $month, $day);
            } else {
                $timestamp = $time[1] + ceil((time() - $time[1]) / $time[0]) * $time[0];
                $datetime = new \DateTime('@' . $timestamp);
            }
            if (null === $next || $datetime < $next) {
                $next = $datetime;
            }
        }
        $next->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        return $next;
    }
    
    protected function timeToDescription($frequency, $offset)
    {
        switch (true) {
        	case $frequency < 3600:
        	    $desc = sprintf("Every %d minuets", $frequency / 60);
        	    if (0 != ($mod = $frequency % 60)) {
        	        $desc .= sprintf(" and %d seconds", $mod);
        	    } 
        	    return $desc . $this->offsetTime($offset, ' starting at');
        	case $frequency == 3600:
        	    return "Every hour" . $this->offsetTime($offset, ' starting at');
        	case $frequency == 86400:
        	    return "Every day" . $this->offsetTime($offset, ' at');
        	case $frequency == 604800:
        	    return "Every week" . $this->offsetWeekday($offset);
        	case $frequency == 2628000:
        	    return "Every month" . $this->offsetDay($offset);
    	    default:
        	    if ($frequency % 86400 == 0) {
        	        return sprintf("Every %d days", $frequency / 86400)
        	               . $this->offsetTime($offset, ' at');
        	    } else {
            	    return sprintf("Every %d hours", $frequency / 3600)
            	           . $this->offsetTime($offset, ' starting at');
        	    }
        }
    }
    
    protected function offsetDay($offset)
    {
        return ' on the ' . date('jS \a\t G:i:s T', $offset);
    }
    
    protected function offsetTime($offset, $prefix = '') 
    {
        if (0 == $offset) {
            return '';
        }
        
        if ($offset < 3600) {
            return $prefix . date(" 0:i:s", $offset);
        }
        
        return $prefix . date(" G:i:s T", $offset);
    }
    
    protected function offsetWeekday($offset)
    {
        return ' on ' . date('l \a\t G:i:s T', $offset);
        if ($offset % 86400 != 0) {
            $weekday .= $this->offsetTime($offset, ' at');
        }
        return $weekday;
    }
}