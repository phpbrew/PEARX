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
use PEARX\PackageXml\FileListInstall;
use PEARX\PackageXml\ContentFile;

class XmlException extends Exception { }

/**
 * PackageXml parser parses package.xml file
 * and returns a package object that contains many 
 * ContentFile, FileListInstall objects.
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
        elseif( file_exists($arg) ) {
            $this->xml = new SimpleXMLElement( file_get_contents( $arg ) );
        }
        else {
            throw new XmlException('Invalid xml argument.');
        }

        $package = new \PEARX\Package;
        $package->setChannel( $this->xml->channel->__toString() );
        $package->setName( $this->xml->name->__toString() );
        $package->setSummary( $this->xml->summary->__toString() );
        $package->setDescription( $this->xml->description->__toString() );
        return $this->xml;
    }

    /**
     * need to support base installdir
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
                    $dirpath .= $dirname;

                // $dirpath = $parentPath ? $parentPath . DIRECTORY_SEPARATOR . $dirname : $dirname;
                if( $baseInstallDir )
                    $dirpath .= $baseInstallDir . DIRECTORY_SEPARATOR;

                if( $dirname != '/' )
                    $dirpath .= DIRECTORY_SEPARATOR;

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
                    ? new ContentFile( $parentPath . $baseInstallDir . DIRECTORY_SEPARATOR . $filename )
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
     *
     */
    public function getPhpReleaseFileList()
    {
        // XXX: some packages like sfYAML uses phprelease tag to use 'install-as'
        $phprelease = $this->xml->phprelease;
        $filelist = array();

        if( $phprelease->filelist ) 
        {
            foreach( $phprelease->filelist->children() as $install ) 
            {
                $filelist[] = new FileListInstall( (string) $install['name'] , (string) @$install['as'] );
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
    public function getContentFilesByRole($role)
    {
        $files = $this->getContentFiles();
        return array_filter( $files , function($item) use ($role) { 
            return $item->role == $role;
        });
    }

    public function getContentFiles()
    {
        $xml = $this->xml;
        $contents = $xml->contents;
        $children = $contents->children();
        return $this->traverseContents( $children );
    }

}

