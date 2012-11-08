<?php
namespace PEARX;
use Phar;
use PharData;
use Exception;

class Installer 
{
    public function install($distPath,$targetDir)
    {
        $distFile = basename($distPath);

        // create temp dir
        // (PHP 5 >= 5.2.1)
        // $tmpDir = sys_get_temp_dir();
        $workspace = 'workspace';
        $packageDir =  $workspace . DIRECTORY_SEPARATOR . time();

        // $cwd = getcwd();
        // chdir( $workspace );
        $archive = new PharData($distFile);
        $archive->extractTo( $packageDir );

        if( ! file_exists($targetDir) )
            mkdir( $targetDir , 0755, true );
        if( ! file_exists($packageDir) )
            mkdir( $packageDir , 0755, true );

        // parse package.xml
        $parser = new \PEARX\PackageXml\Parser;
        $package = $parser->parse( $packageDir . DIRECTORY_SEPARATOR . 'package.xml' );

        $sourceDir = $packageDir . DIRECTORY_SEPARATOR . $package->getName() . '-' . $package->getReleaseVersion();
        $filelist = $package->getInstallFileList( $sourceDir, $targetDir );

        $installedCnt = 0;
        foreach( $filelist as $install ) {
            $dir = dirname( $install->to );
            if( ! file_exists( $dir ) )
                mkdir( $dir , 0755 , true );
            copy( $install->from , $install->to );
            $installedCnt++;
        }
        return $installedCnt;
    }
}



