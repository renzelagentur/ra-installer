<?php

namespace Ra\Installer;

use \Composer\Package\PackageInterface;
use \Composer\Installer\LibraryInstaller;


class RaInstaller extends LibraryInstaller
{
    protected $locations = array(
        'ra-module'    => 'htdocs/modules/ra',
        'ra-theme'    => 'htdocs/application/views',
        'oxid-base'    => 'htdocs'
    );

    public function getInstallPath(PackageInterface $package) {
        if ($package->getType() === 'oxid-base') {
            return $this->locations[$package->getType()];
        }
        $themeName = "ra";
        $extra = $package->getExtra();
        
        $installFolder=$this->locations[$package->getType()] . '/' . str_replace(array($themeName."/", '-theme'), '', $package->getPrettyName());
        
        if ($extra) {
            $themeName=$extra["themeName"]; 
            $installFolder = $this->locations[$package->getType()] . '/' . $themeName;
        }
        
        return $installFolder;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
       return isset($this->locations[$packageType]);
    }
}
