<?php
namespace PEARX;
use Exception;
use DateTime;
use PEARX\Channel;

class Package
{
    public $name;

    public $summary;

    public $description;


    /**
     * @var string date
     */
    public $date;

    /**
     * @var string time
     */
    public $time;


    private $_datetime;

    /**
     * @var PEARX\Channel
     */
    public $channel;


    /**
     * @var string license
     */
    public $license;

    public $authors = array();

    public $apiVersion;

    public $releaseVersion;

    public $apiStability;

    public $releaseStability;

    public function setName($name) 
    {
        $this->name = $name;
    }

    public function setVersion($version)
    {
        $this->apiVersion = $version;
        $this->releaseVersion = $version;
    }

    public function setApiVersion($version)
    {
        $this->apiVersion = $version;
    }

    public function setReleaseVersion($version)
    {
        $this->releaseVersion = $version;
    }

    public function setStability($s) 
    {
        $this->apiStability = $s;
        $this->releaseStability = $s;
    }

    public function setReleaseStability($s)
    {
        $this->releaseStability = $s;
    }

    public function setApiStability($s)
    {
        $this->apiStability = $s;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getDateTime()
    {
        if( $this->date && $this->time ) {
            return new DateTime( 
                sprintf('%s %s', $this->date, $this->time)
            );
        }
        if( $this->_datetime ) {
            return $this->_datetime;
        }
    }

    public function setDateTime($datetime)
    {
        if ( is_string($datetime) ) {
            // parse string
            $datetime = new DateTime($datetime);
        }

        if( $datetime instanceof DateTime ) {
            $this->date = $datetime->format('Y-m-d');
            $this->time = $datetime->format('H:i:s');
            $this->_datetime = $datetime;
        } else {
            throw new Exception("Invalid DateTime format.");
        }
    }

    public function setDate($date) 
    {
        $this->date = $date;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setChannel($c)
    {
        if( is_string($c) ) {
            $this->channel = new Channel($c);
        } elseif( $c instanceof Channel ) {
            $this->channel = $c;
        } else {
            throw new Exception("Unknown channel argument.");
        }
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function getLicense()
    {
        return $this->license;
    }

    public function setSummary($summary)
    {
        $this->summary = $summary;
    }


    public function getSummary()
    {
        return $this->summary;
    }


    public function setDescription($desc)
    {
        $this->description = $desc;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getId()
    {
        return $this->name;
    }

}

