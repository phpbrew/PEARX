<?php
namespace PEARX;
use Phar;
use PharData;
use Exception;
use PEARX\PackageXml\Parser as PackageXmlParser;

class Installer 
{
    public $workspace = 'workspace';

    public function setWorkspace($path)
    {
        $this->workspace = $path;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }


    /**
     *
     * {workspace}/{package name}_{timestamp}
     *
     * workspace/cliframework_1_5_7_tgz_1352398203
     */
    public function getWorkspaceForPackage($distPath)
    {
        $name = strtolower(preg_replace('#\W+#','_',basename($distPath))) . '_' . time();
        return $this->getWorkspace() . DIRECTORY_SEPARATOR . $name;
    }

    public function install($distPath,$targetDir)
    {
        $distFile = basename($distPath);
        $packageDir = $this->getWorkspaceForPackage($distPath);

        $this->extract($distPath,$packageDir);

        if( ! file_exists($targetDir) )
            mkdir( $targetDir , 0755, true );
        if( ! file_exists($packageDir) )
            mkdir( $packageDir , 0755, true );

        // parse package.xml
        $parser = new PackageXmlParser;
        $package = $parser->parse( $packageDir . DIRECTORY_SEPARATOR . 'package.xml' );

        $sourceDir = $packageDir . DIRECTORY_SEPARATOR . $package->getName() . '-' . $package->getReleaseVersion();
        $filelist = $package->getInstallFileList( $sourceDir, $targetDir );

        // XXX: Improve this
        foreach( $filelist as $install ) {
            $dir = dirname( $install->to );
            if( ! file_exists( $dir ) )
                mkdir( $dir , 0755 , true );

            // XXX: Add md5sum checks
            copy( $install->from , $install->to );
        }
        return $filelist;
    }

    public function extract($file,$targetPath)
    {
        $archive = new PharData($file);
        $archive->extractTo( $targetPath );
        return $archive;
    }


}



