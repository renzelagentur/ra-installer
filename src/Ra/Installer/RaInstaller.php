<?php

namespace Ra\Installer;

use \Composer\Package\PackageInterface;
use \Composer\Installer\LibraryInstaller;
use \Composer\Repository\InstalledRepositoryInterface;

use Composer\Script\Event;

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

	public static function postUpdate(Event $event) {
		self::cleanUpOldModules($event);
	}

	public static function postInstall(Event $event) {
		self::cleanUpOldModules($event);
	}

	protected static function cleanUpOldModules(Event $event) {

		$composer = $event->getComposer();
		$requires = $composer->getPackage()->getRequires();

		$projectPath = str_replace('vendor/vkf/shop', '', $composer->getInstallationManager()->getInstallpath($composer->getPackage()));
		$modulePath = $projectPath . 'htdocs/modules';
		foreach (array('ra', 'vkf') as $renzelVendorDir) {
			foreach (glob($modulePath . '/' . $renzelVendorDir . '/*') as $file) {
				if (is_dir($file)) {
					$packageId = str_replace($modulePath . '/', '', $file);
					if (!isset($requires[$packageId])) {
						self::rrmdir($file);
						$event->getIO()->write('Deleted old package ' . $packageId);
					}
				}
			}

		}

	}

	protected static function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") self::rrmdir($dir."/".$object); else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

}
