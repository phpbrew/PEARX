<?php
namespace PEARX;

class Package
{
    public $name;

    public $summary;

    public $desc;

    /**
     * @var Channel
     */
    public $channel;


    /**
     * @var string license
     */
    public $license;

    public $releases = array();

    public $versions = array();


    /**
     * Dependency of versions.
     */
    public $deps = array();


    /**
     * Last stable version.
     */
    public $stable;


    /**
     * Last alpha version.
     */
    public $alpha;

    /**
     * Last beta version.
     */
    public $beta;

    /**
     * Latest version.
     */
    public $latest;


    public function setName($name) 
    {
        $this->name = $name;
    }

    public function setChannel($url)
    {
        $this->channel = $url;
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @param string $version version string.
     * @param string $stability stability string.
     */
    public function addRelease( $version , $stability )
    {
        $this->releases[] = (object) array( 
            'version' => $version,
            'stability' => $stability,
        );
        $this->versions[ $version ] = $stability;
    }

    public function getRelease( $version ) 
    {
        if( isset( $this->versions[ $version ] ) )
            return $this->versions[ $version ];
    }

    public function getReleaseDistUrl($version, $extension = 'tgz' )
    {
        // xxx: save for https or http base url from channel discover object
        // return sprintf( 'http://%s/get/%s-%s.%s' , $this->channel, $this->name , $version , $extension );
        return sprintf('%s/get/%s-%s.%s',$this->channel->getBaseUrl(), $this->name , $version , $extension );
    }

    public function getLastReleaseDistUrl()
    {
        return $this->getReleaseDistUrl( $this->latest );
    }

    public function getId()
    {
        return $this->name;
    }
}

