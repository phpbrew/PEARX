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

    public function setDateTime($datetime)
    {
        if ( is_string($datetime) ) {
            // parse string
            $datetime = new DateTime($datetime);
        }

        if( $datetime instanceof DateTime ) {
            $this->date = $datetime->format('Y-m-d');
            $this->time = $datetime->format('H:i:s');
        } else {
            throw new Exception("Invalid DateTime format.");
        }
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

    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    public function setDescription($desc)
    {
        $this->description = $desc;
    }

    public function getId()
    {
        return $this->name;
    }
}

