<?php

namespace Ra\Installer;

use \Composer\Package\PackageInterface;
use \Composer\Installer\LibraryInstaller;
use \Composer\Repository\InstalledRepositoryInterface;


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

	/**
	 * Generates a vendor metadata file
	 */
	protected function generateVendorMetaData() {
		touch('htdocs/modules/ra/vendormetadata.php');
	}

	/**
	 * Installs specific package.
	 *
	 * @param InstalledRepositoryInterface $repo    repository in which to check
	 * @param PackageInterface             $package package instance
	 */
	public function install(InstalledRepositoryInterface $repo, PackageInterface $package) {
		parent::install($repo, $package);
		$this->generateVendorMetaData();
	}


	/**
	 * Updates specific package.
	 *
	 * @param InstalledRepositoryInterface $repo    repository in which to check
	 * @param PackageInterface             $initial already installed package version
	 * @param PackageInterface             $target  updated version
	 *
	 * @throws InvalidArgumentException if $initial package is not installed
	 */
	public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target) {
		parent::update($repo, $initial, $target);
		$this->generateVendorMetaData();
	}
}
