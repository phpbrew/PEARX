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

    public $contents = array();

    public $requiredDependencies = array();

    public $optionalDependencies = array();

    public $phpReleaseFileList = array();

    public $providesExtension;

    public $zendExtSrcRelease;

    public function setName($name) 
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
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

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setReleaseVersion($version)
    {
        $this->releaseVersion = $version;
    }

    public function getReleaseVersion()
    {
        return $this->releaseVersion;
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

    public function setProvidesExtension($extName) {
        $this->provideExtension = $extName;
    }

    public function setZendExtSrcRelease() {
        $this->zendExtSrcRelease = true;
    }

    public function getProvidesExtension() {
        return $this->provideExtension;
    }

    public function getZendExtSrcRelease() {
        return $this->zendExtSrcRelease;
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

    public function addContent(PackageXml\ContentFile $content)
    {
        $this->contents[] = $content;
    }

    public function setContents($contents)
    {
        $this->contents = $contents;
    }

    public function getContents()
    {
        return $this->contents;
    }

    /**
     * get FileContent objects by role
     *
     * @param string $role role string.
     *
     * @return PEARX\PackageXml\ContentFile[]
     */
    public function getContentsByRole($role)
    {
        return array_filter($this->contents , function($item) use ($role) {
            return $item->role === $role;
        });
    }



    public function validateDependencyType($type)
    {
        if(    $type != 'php'
            && $type != 'pearinstaller'
            && $type != 'extension'
            && $type != 'package' )
            throw new Exception('invalid pear dependency type.');

    }

    /**
     * for php:
     *
     * $this->addRequiredDependency('php',array( 'min' => '5.2.7' ));
     *
     * for pearinstaller:
     *
     * $this->addRequiredDependency('pearinstaller',array( 'min' => '1.9.4' ));
     *
     * for package:
     *
     * $this->addRequiredDependency('package',array( 
     *    'name' => 'Text_Template',
     *    'channel' => 'pear.phpunit.de',
     *    'min' => '1.1.1',
     * ));
     *
     * $this->addRequiredDependency('extension',array(
     *     'name' => 'reflection',
     * ));
     *
     * $this->addRequiredDependency('extension',array(
     *     'name' => 'spl',
     * ));
     */
    public function addRequiredDependency($type,$dep)
    {
        // validate dep inforamtion
        $this->validateDependencyType($type);
        $dep['type'] = $type;
        $this->requiredDependencies[] = $dep;
    }

    public function addOptionalDependency($type,$dep)
    {
        $this->validateDependencyType($type);
        $dep['type'] = $type;
        $this->optionalDependencies[] = $dep;
    }

    public function addFileToReleaseFileList($name,$as)
    {
        $this->phpReleaseFileList[$name] = $as;
    }

    public function getReleaseFileList()
    {
        return $this->phpReleaseFileList;
    }



    /**
     * Get filelist array for installer.
     *
     * @param string $sourceDir source directory path
     */
    public function getInstallFileList($sourceDir = null, $distDir = null)
    {
        $installMap = $this->getReleaseFileList();
        $filelist = array();
        foreach( $this->getContents() as $file ) 
        {
            if( $file->role == 'php' ) {
                $installFrom = $sourceDir 
                    ? $sourceDir . DIRECTORY_SEPARATOR . $file->file
                    : $file->file;
                if( isset($installMap[ $file->file ]) ) {
                    $installAs = $installMap[ $file->file ];
                } else {
                    $installAs = $file->getInstallAs();
                }

                if ( $distDir ) {
                    $installAs = $distDir . DIRECTORY_SEPARATOR . $installAs;
                }

                $filelist[] = (object) array(
                    'from' => $installFrom,
                    'to'   => $installAs,
                );
            }
        }
        return $filelist;
    }


}

