<?php
/**
 * This file is part of the PEARX package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace PEARX\PackageXml;
use SimpleXMLElement;
use Exception;
use PEARX\PackageXml\ContentFile;

class XmlException extends Exception { }

/**
 * PackageXml parser parses package.xml file
 * and returns a package object that contains many 
 * ContentFile objects.
 */
class Parser
{
    public $xml;

    public function __construct()
    {
    }

    public function parse($arg) 
    {
        if( strpos($arg,'<?xml') === 0 ) {
            $this->xml = new SimpleXMLElement( $arg );
        }
        elseif( file_exists($arg) || preg_match('#^https?://#',$arg) ) {
            $this->xml = new SimpleXMLElement( file_get_contents( $arg ) );
        }
        else {
            throw new XmlException('Invalid xml argument.');
        }
        $xml = $this->xml;

        $package = new \PEARX\Package;
        $package->setChannel( $xml->channel->__toString() );
        $package->setName( $xml->name->__toString() );
        $package->setSummary( $xml->summary->__toString() );
        $package->setDescription( $xml->description->__toString() );
        $package->setDate( $xml->date->__toString() );
        $package->setTime( $xml->time->__toString() );

        $package->setApiVersion( $xml->version->api->__toString() );
        $package->setReleaseVersion( $xml->version->release->__toString() );

        $package->setApiStability( $xml->stability->api->__toString() );
        $package->setReleaseStability( $xml->stability->release->__toString() );

        if ($extName = $xml->providesextension->__toString()) {
            $package->setProvidesExtension($extName);
        }

        if ($extsrcrelease = $xml->extsrcrelease) {
            foreach ($extsrcrelease->children() as $opt) {
                $package->addConfigureOption($opt['name'], $opt['prompt'], $opt['default']);
            }
        }

        if ($xml->zendextsrcrelease) {
            $package->setZendExtSrcRelease();
        }

        /*
        $package->setZendExtSrcRelease();
         */

        $package->setContents( $this->parseContents($xml) );

        if( $xml->dependencies->required ) {
            $deps = $this->parseDependencyElement($xml->dependencies->required);
            foreach( $deps as $dep )
                $package->addRequiredDependency($dep['type'],$dep);
        }

        if( $xml->dependencies->optional ) {
            $deps = $this->parseDependencyElement($xml->dependencies->optional);
            foreach( $deps as $dep )
                $package->addOptionalDependency($dep['type'],$dep);
        }

        // parse php release tag
        if( $phprelease = $xml->phprelease ) {
            if( $phprelease->filelist ) {
                foreach( $phprelease->filelist->children() as $install ) {
                    $package->addFileToReleaseFileList( $install['name']->__toString(), $install['as']->__toString() );
                }
            }
        }
        return $package;
    }



    /**
     * Need to support base installdir
     */
    public function traverseContents($children, $parentPath = null )
    {
        $files = array();
        foreach( $children as $node ) {
            if( $node->getName() == 'dir' ) {
                $dirname = $node['name'];
                $baseInstallDir = @$node['baseinstalldir'];

                $dirpath = $parentPath;
                if( $dirname != '/' )
                    $dirpath .= $dirname . DIRECTORY_SEPARATOR;

                // $dirpath = $parentPath ? $parentPath . DIRECTORY_SEPARATOR . $dirname : $dirname;
                if( $baseInstallDir )
                    $dirpath .= ltrim($baseInstallDir,DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

                $subfiles = $this->traverseContents( $node->children(), $dirpath );
                $files = array_merge( $files , $subfiles );
            }
            elseif( $node->getName() == 'file' ) {
                $filename       = (string) $node['name'];
                $installAs      = (string) @$node['install-as'];
                $baseInstallDir = (string) @$node['baseinstalldir'];
                $role           = (string) @$node['role'];
                $md5sum         = (string) @$node['md5sum'];

                $file = $baseInstallDir
                    ? new ContentFile( $parentPath . ltrim($baseInstallDir,DIRECTORY_SEPARATOR). $filename )
                    : new ContentFile( $parentPath . $filename );

                if( $installAs )
                    $file->installAs = $parentPath . $installAs;

                $file->setRole($role);
                $file->md5sum = $md5sum;
                $files[] = $file;
            }
        }
        return $files;
    }



    /**
     * The release file list provides install-as list for installer.
     */
    public function getPhpReleaseFileList()
    {
        // XXX: some packages like sfYAML uses phprelease tag to use 'install-as'
        $phprelease = $this->xml->phprelease;
        $filelist = array();
        if( $phprelease->filelist ) {
            foreach( $phprelease->filelist->children() as $install ) 
            {
                $filelist[] = (object) array(
                    'file' => (string) $install['name'], 
                    'as' => (string) @$install['as']
                );
            }
        }
        return $filelist;
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
        $files = $this->getContentFiles();
        return array_filter( $files , function($item) use ($role) { 
            return $item->role == $role;
        });
    }

    /**
     * Parse <contents> section and return content data 
     * structure.
     */
    public function parseContents($xml)
    {
        return $this->traverseContents( 
            $xml->contents->children()
        );
    }


    public function parseDependencyElement($parent)
    {
        $children = $parent->children();
        $deps = array();
        foreach( $children as $element ) {
            $attrs = array();
            foreach( $element->children() as $attr ) {
                $attrs[ $attr->getName() ] = $attr->__toString();
            }
            $attrs['type'] = $element->getName();
            $deps[] = $attrs;
        }
        return $deps;
    }



    // XXX: DEPRECATED.
    public function getContentFiles()
    {
        return $this->parseContents($this->xml);
    }

    // XXX: DEPRECATED.
    public function getContentFilesByRole($role)
    {
        return $this->getContentsByRole($role);
    }




}

