<?php

// autoload_real.php @generated by Composer

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
class ComposerAutoloaderInitDrupal8
=======
class ComposerAutoloaderInit21da3f90acc4075b2c94db7757e92f69
>>>>>>> Based off of manual_composer first commit
=======
class ComposerAutoloaderInit0495cb476748113cd8c2b650669189aa
>>>>>>> Site is upgraded and working
=======
class ComposerAutoloaderInitfc5c072bbc18e8af9e5599ba42ad535d
>>>>>>> Pulled DB from Live
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

<<<<<<< HEAD
<<<<<<< HEAD
        spl_autoload_register(array('ComposerAutoloaderInitDrupal8', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader();
        spl_autoload_unregister(array('ComposerAutoloaderInitDrupal8', 'loadClassLoader'));
=======
        spl_autoload_register(array('ComposerAutoloaderInit21da3f90acc4075b2c94db7757e92f69', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader();
        spl_autoload_unregister(array('ComposerAutoloaderInit21da3f90acc4075b2c94db7757e92f69', 'loadClassLoader'));
>>>>>>> Based off of manual_composer first commit
=======
        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitfc5c072bbc18e8af9e5599ba42ad535d', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader();
<<<<<<< HEAD
        spl_autoload_unregister(array('ComposerAutoloaderInit0495cb476748113cd8c2b650669189aa', 'loadClassLoader'));
>>>>>>> Site is upgraded and working
=======
        spl_autoload_unregister(array('ComposerAutoloaderInitfc5c072bbc18e8af9e5599ba42ad535d', 'loadClassLoader'));
>>>>>>> Pulled DB from Live

        $includePaths = require __DIR__ . '/include_paths.php';
        $includePaths[] = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, $includePaths));

        $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
        if ($useStaticLoader) {
            require __DIR__ . '/autoload_static.php';

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
            call_user_func(\Composer\Autoload\ComposerStaticInitDrupal8::getInitializer($loader));
=======
            call_user_func(\Composer\Autoload\ComposerStaticInit21da3f90acc4075b2c94db7757e92f69::getInitializer($loader));
>>>>>>> Based off of manual_composer first commit
=======
            call_user_func(\Composer\Autoload\ComposerStaticInit0495cb476748113cd8c2b650669189aa::getInitializer($loader));
>>>>>>> Site is upgraded and working
=======
            call_user_func(\Composer\Autoload\ComposerStaticInitfc5c072bbc18e8af9e5599ba42ad535d::getInitializer($loader));
>>>>>>> Pulled DB from Live
        } else {
            $map = require __DIR__ . '/autoload_namespaces.php';
            foreach ($map as $namespace => $path) {
                $loader->set($namespace, $path);
            }

            $map = require __DIR__ . '/autoload_psr4.php';
            foreach ($map as $namespace => $path) {
                $loader->setPsr4($namespace, $path);
            }

            $classMap = require __DIR__ . '/autoload_classmap.php';
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
        }

        $loader->register(true);

        if ($useStaticLoader) {
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
            $includeFiles = Composer\Autoload\ComposerStaticInitDrupal8::$files;
=======
            $includeFiles = Composer\Autoload\ComposerStaticInit21da3f90acc4075b2c94db7757e92f69::$files;
>>>>>>> Based off of manual_composer first commit
=======
            $includeFiles = Composer\Autoload\ComposerStaticInit0495cb476748113cd8c2b650669189aa::$files;
>>>>>>> Site is upgraded and working
=======
            $includeFiles = Composer\Autoload\ComposerStaticInitfc5c072bbc18e8af9e5599ba42ad535d::$files;
>>>>>>> Pulled DB from Live
        } else {
            $includeFiles = require __DIR__ . '/autoload_files.php';
        }
        foreach ($includeFiles as $fileIdentifier => $file) {
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
            composerRequireDrupal8($fileIdentifier, $file);
=======
            composerRequire21da3f90acc4075b2c94db7757e92f69($fileIdentifier, $file);
>>>>>>> Based off of manual_composer first commit
=======
            composerRequire0495cb476748113cd8c2b650669189aa($fileIdentifier, $file);
>>>>>>> Site is upgraded and working
=======
            composerRequirefc5c072bbc18e8af9e5599ba42ad535d($fileIdentifier, $file);
>>>>>>> Pulled DB from Live
        }

        return $loader;
    }
}

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
function composerRequireDrupal8($fileIdentifier, $file)
=======
function composerRequire21da3f90acc4075b2c94db7757e92f69($fileIdentifier, $file)
>>>>>>> Based off of manual_composer first commit
=======
function composerRequire0495cb476748113cd8c2b650669189aa($fileIdentifier, $file)
>>>>>>> Site is upgraded and working
=======
function composerRequirefc5c072bbc18e8af9e5599ba42ad535d($fileIdentifier, $file)
>>>>>>> Pulled DB from Live
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        require $file;

        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
    }
}
