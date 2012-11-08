<?php
namespace PEARX;
use Phar;
use PharData;
use Exception;

class Installer 
{


    public function extract($file,$targetPath)
    {
        $archive = new PharData($file);
        $archive->extractTo( $targetPath );
        return $archive;
    }

    public function install($distPath,$targetDir)
    {
        $distFile = basename($distPath);
        $workspace = 'workspace';
        $packageDir =  $workspace . DIRECTORY_SEPARATOR . time();

        $this->extract($distFile,$packageDir);

        if( ! file_exists($targetDir) )
            mkdir( $targetDir , 0755, true );
        if( ! file_exists($packageDir) )
            mkdir( $packageDir , 0755, true );

        // parse package.xml
        $parser = new \PEARX\PackageXml\Parser;
        $package = $parser->parse( $packageDir . DIRECTORY_SEPARATOR . 'package.xml' );

        $sourceDir = $packageDir . DIRECTORY_SEPARATOR . $package->getName() . '-' . $package->getReleaseVersion();
        $filelist = $package->getInstallFileList( $sourceDir, $targetDir );

        // XXX: Improve this
        foreach( $filelist as $install ) {
            $dir = dirname( $install->to );
            if( ! file_exists( $dir ) )
                mkdir( $dir , 0755 , true );
            copy( $install->from , $install->to );
        }
        return $filelist;
    }
}



