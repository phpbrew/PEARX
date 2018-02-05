<?php

namespace PEARX;

use DOMDocument;
use Exception;

/**
 * ````php
 * $channel = new PEARX\Channel( 'pear.php.net', array(
 *    'cache' => ....
 * ));
 *
 * $channel->getPackages();
 * ```
 */
class Channel
{
    /**
     * @var string channel host name
     */
    public $name;

    /**
     * @var string suggested alias
     */
    public $alias;

    /**
     * @var string summary
     */
    public $summary;

    /**
     * primary server
     */
    public $primary = array();

    /**
     * @var string REST version
     */
    public $rest; // Latest REST version

    public $cache;

    public $downloader;

    public $retry = 3;

    public $channelXml;

    /**
     * channel url scheme
     */
    public $scheme = 'http';

    public $core;

    private $host;
    private $init = false;

    /**
     * @param string $host
     * @param array $options
     */
    public function __construct($host, $options = array() )
    {
        $this->core = new Core( $options );
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        $this->init();

        return $this->scheme . '://' . $this->name;
    }

    /**
     * @param string $version
     *
     * @return string
     */
    public function getRestBaseUrl($version = null)
    {
        $this->init();

        if( $version && $this->primary[$version] ) {
            return rtrim($this->primary[ $version ],'/');
        }

        return rtrim($this->primary[ $this->rest ],'/');
    }


    /**
     * @param string $packageName
     * @param string $version
     *
     * @return DOMDocument
     */
    public function fetchPackageReleaseXml($packageName, $version = 'stable')
    {
        $baseUrl = $this->getRestBaseUrl();
        $url = "$baseUrl/r/" . strtolower($packageName);

        if ( $version === 'stable'
            || $version === 'latest'
            || $version === 'beta'
        ) {
            // Get version info
            $ret = file_get_contents($url . '/' . $version . '.txt');
            if ($ret === false) {
                throw new Exception("Invalid package version: $packageName with version '$version'.");
            }
            $version = $ret;
        }

        $url = $url . '/' . $version . '.xml';
        $xmlStr = $this->core->request($url);

        // libxml_use_internal_errors(true);
        $xml = Utils::create_dom();
        if( false === $xml->loadXml( $xmlStr ) ) {
            throw new Exception("Error in XMl document: $url");
        }
        return $xml;
    }

    /**
     * fetch channel.xml from PEAR channel server.
     *
     * @param string $host
     *
     * @return string
     */
    public function fetchChannelXml($host)
    {
        $xmlstr = $this->core->cache ? $this->core->cache->get( $host ) : null;

        // Check the cache
        if( null !== $xmlstr ) {
            return $xmlstr;
        }

        $httpUrl = 'http://' . $host . '/channel.xml';
        $httpsUrl = 'https://' . $host . '/channel.xml';
        while( $this->retry-- ) {
            try {
                if( $xmlstr = $this->core->request($httpUrl)  ) {
                    $this->scheme = 'http';
                    break;
                }
                if( $xmlstr = $this->core->request( $httpsUrl ) ) {
                    $this->scheme = 'https';
                    break;
                }
            } catch( Exception $e ) {
                fwrite( STDERR , "PEAR Channel discover failed: $host\n" );
                fwrite( STDERR , $e->getMessage() . "\n" );
            }
        }

        if( ! $xmlstr ) {
            throw new Exception('channel.xml fetch failed.');
        }

        // Cache result
        if( $this->cache ) {
            $this->cache->set($host, $xmlstr );
        }
        return $xmlstr;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        $baseUrl = $this->getRestBaseUrl();
        $url = $baseUrl . '/c/categories.xml';
        $xmlStr = $this->core->request($url);

        // libxml_use_internal_errors(true);
        $xml = Utils::create_dom();
        if( false === $xml->loadXml( $xmlStr ) ) {
            throw new Exception("Error in XMl document: $url");
        }

        $list = array();
        $nodes = $xml->getElementsByTagName('c');
        foreach ($nodes as $node) {
            // path like: /rest/c/Default/info.xml
            $link = $node->getAttribute("xlink:href");
            $name = $node->nodeValue;
            $category = new Category( $this, $name , $link );
            $list[] = $category;
        }
        return $list;
    }


    /**
     * Get all packages from this channel.
     *
     * @return Package[]
     */
    public function getPackages()
    {
        $packages = array();
        foreach( $this->getCategories() as $category ) {
            $packages[ $category->name ] = $category->getPackages();
        }
        return $packages;
    }


    /**
     * @param string
     *
     * @return Package|null
     */
    public function findPackage($name)
    {
        foreach( $this->getCategories() as $category ) {
            $packages = $category->getPackages();
            if( isset($packages[$name]) ) {
                return $packages[$name];
            }
        }
    }

    /**
     * Initialise the properties necessary to do an HTTP request. This is done lazily as opposed as during
     * instantiation to avoid an HTTP request if unnecessary which would otherwise fail if no internet
     * connection is available.
     */
    private function init()
    {
        if ($this->init) {
            return;
        }

        $this->channelXml = $this->fetchChannelXml( $this->host );

        $parser = new ChannelParser;
        $info = $parser->parse( $this->channelXml );

        $this->name = $info->name;
        $this->summary = $info->summary;
        $this->alias = $info->alias;
        $this->primary = $info->primary;
        $this->rest = $info->rest;

        $this->init = true;
    }
}


