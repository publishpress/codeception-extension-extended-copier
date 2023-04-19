<?php
/**
 * An extension that will copy directories and files from the source the destination before tests.
 * Extends the original Copier class to allow copying the same file to multiple destinations, and
 * to automatically create parent directories if not existing.
 *
 * @package tad\WPBrowser\Extension
 */

namespace PublishPress\Codeception\Extension;

use Codeception\Exception\ExtensionException;
use Codeception\Lib\Console\Output;
use tad\WPBrowser\Extension\Copier;

use function tad\WPBrowser\recurseCopy;

/**
 * Class Copier
 *
 * @package tad\WPBrowser\Extension
 */
class ExtendedCopier extends Copier
{
    protected $sources = [];

    protected $files = [];

    /**
     * Copier constructor.
     *
     * @param array<string,mixed> $config The extension configuration.
     * @param array<string,mixed> $options The extension options.
     */
    public function __construct($config, $options)
    {
        if (! empty($config['files'])) {
            $this->sources = array_map(function ($item) {
                $paths = explode(':', $item);
                return realpath($paths[0]);
            }, $config['files']);

            $this->files = array_map(function ($item) {
                $paths = explode(':', $item);
                return $paths[1];
            }, $config['files']);


            array_walk($this->sources, [$this, 'ensureSource']);
            array_walk($this->files, [$this, 'ensureDestination']);
        }

        $this->config = array_merge($this->config, $config);
        $this->options = $options;
        $this->output = new Output($options);
        $this->_initialize();
    }

    /**
     * Copies the directory and files from the extension configuration.
     *
     * @return void
     */
    public function copyFiles()
    {
        if (empty($this->files)) {
            return;
        }

        array_walk($this->files, [$this, 'copy']);
    }

    /**
     * Removes the copied directories and files.
     *
     * @return void
     */
    public function removeFiles()
    {
        if (empty($this->files)) {
            return;
        }

        array_walk($this->files, [$this, 'remove']);
    }

    /**
     * Checks a destination directory or file are accessible.
     * Different from the parent class, it will create any parent
     * directory if not existing, instead of fail.
     *
     * @param string $destination The path to the copy destination.
     *
     * @return void
     *
     * @throws ExtensionException If the destination is not accessible.
     */
    protected function ensureDestination($destination)
    {
        $filename = dirname($destination);

        if (! (is_dir($filename))) {
            mkdir($filename, 0777, true);
        }

        if (! is_writable($filename)) {
            throw new ExtensionException($this, sprintf('Destination parent dir [%s] is not writeable.', $filename));
        }

        if (file_exists($destination)) {
            $this->remove($destination);
        }
    }

    /**
     * Checks the source to ensure it's accessible and readable.
     *
     * @param string $source The path to the source directory or file.
     *
     * @return void
     *
     * @throws ExtensionException If the source directory or file is not readable or not accessible.
     */
    protected function ensureSource($source)
    {
        if (! (
            file_exists($source)
            || file_exists(getcwd() . DIRECTORY_SEPARATOR . trim($source, DIRECTORY_SEPARATOR))
        )) {
            debug_print_backtrace();
            throw new ExtensionException($this, sprintf('Source file [%s] does not exist.', $source));
        }

        if (! is_readable($source)) {
            throw new ExtensionException($this, sprintf('Source file [%s] is not readable.', $source));
        }
    }


    /**
     * Removes a previously created destination directory or file.
     *
     * @param string $destination The absolute path to the destination to remove.
     *
     * @return void
     *
     * @throws ExtensionException If the destination directory of file removal fails.
     */
    protected function remove($destination)
    {
        if (! file_exists($destination)) {
            return;
        }

        if (! \tad\WPBrowser\rrmdir($destination)) {
            throw new ExtensionException($this, sprintf('Removal of [%s] failed.', $destination));
        }
    }

    /**
     * Copies one source to one destination.
     *
     * @param string $destination The absolute path to the destination.
     * @param string $source The absolute path to the source.
     *
     * @return void
     *
     * @throws ExtensionException If the copy from the source to the destination fails.
     */
    protected function copy($destination, $source)
    {
        $source = $this->sources[$source];

        if (! is_dir($source)) {
            copy($source, $destination);
            return;
        }
        if (! recurseCopy($source, $destination)) {
            throw new ExtensionException(
                $this,
                sprintf('Copy of [%s:%s] failed.', $source, $destination)
            );
        }
    }
}
