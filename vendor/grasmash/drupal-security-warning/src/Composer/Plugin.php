<?php

/**
 * @file
 * Generates a warning for installation of Drupal packages not supported by Security Team.
 */

namespace grasmash\DrupalSecurityWarning\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvents;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{

    /**
     * @var Composer $composer
     */
    protected $composer;

    /**
     * @var IOInterface $io
     */
    protected $io;

    /**
     * @var array $unsupportedPackages
     */
    protected $unsupportedPackages = [];

    /**
     * Apply plugin modifications to composer
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @see https://getcomposer.org/doc/articles/scripts.md#event-names
     */
    public static function getSubscribedEvents()
    {
        return array(
            PackageEvents::POST_PACKAGE_INSTALL => "onPostPackageEvent",
            PackageEvents::POST_PACKAGE_UPDATE => "onPostPackageEvent",
            ScriptEvents::POST_INSTALL_CMD => 'onPostCmdEvent',
            ScriptEvents::POST_UPDATE_CMD => 'onPostCmdEvent',
        );
    }

    /**
     * Adds package to $this->unsupportedPackages if applicable.
     *
     * @param \Composer\Installer\PackageEvent $event
     */
    public function onPostPackageEvent(\Composer\Installer\PackageEvent $event)
    {
        $package = $this->getDrupalPackage($event->getOperation());
        if ($package) {
            if (!$this->isPackageSupported($package)) {
                $this->unsupportedPackages[$package->getName()] = $package;
            }
        }
    }

    /**
     * Checks to see if this Drupal package is supported by the Drupal Security Team.
     *
     * @param \Composer\Package\PackageInterface $package
     * @return bool
     */
    protected function isPackageSupported($package)
    {
        $extra = $package->getExtra();
        if (!empty($extra['drupal']['security-coverage']['status'])
          && $extra['drupal']['security-coverage']['status'] == 'not-covered') {
            return false;
        }
        return true;
    }

    /**
     * Execute blt update after update command has been executed, if applicable.
     *
     * @param \Composer\Script\Event $event
     */
    public function onPostCmdEvent(\Composer\Script\Event $event)
    {
        if (!empty($this->unsupportedPackages)) {
            $this->io->write(
                '<error>You are using Drupal packages that are not supported by the Drupal Security Team!</error>'
            );
            foreach ($this->unsupportedPackages as $package_name => $package) {
                $extra = $package->getExtra();
                $this->io->write(
                    "  - <comment>$package_name:{$package->getVersion()}</comment>: {$extra['drupal']['security-coverage']['message']}"
                );
            }
            $this->io->write(
                '<comment>See https://www.drupal.org/security-advisory-policy for more information.</comment>'
            );
        }
    }

    /**
     * Gets the package if it is a Drupal related package.
     *
     * @param $operation
     *
     * @return mixed
     *   If the package is a Drupal package, it will be returned. Otherwise, NULL.
     */
    protected function getDrupalPackage($operation)
    {
        if ($operation instanceof InstallOperation) {
            $package = $operation->getPackage();
        } elseif ($operation instanceof UpdateOperation) {
            $package = $operation->getTargetPackage();
        }
        if ($this->isDrupalPackage($package)) {
            return $package;
        }
        return null;
    }

    /**
     * Checks to see if a given package is a Drupal package.
     *
     * @param $package
     *
     * @return bool
     *   TRUE if the package is a Drupal package.
     */
    protected function isDrupalPackage($package)
    {
        if (isset($package) && $package instanceof PackageInterface && strstr($package->getName(), 'drupal/')) {
            return true;
        }
        return false;
    }
}
