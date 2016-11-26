<?php

require_once('src/IFileStorage.php');
/**
 * Created by PhpStorm.
 * User: sebi
 * Date: 24.11.16
 * Time: 15:08
 */
class FileSystem implements IFileStorage
{

    /**
     * @param string $filename
     * @return string
     */
    public function readFile(string $filename) : string
    {
        return file_get_contents($filename);
    }

    public function writeFile(string $filename, string $content)
    {
        file_put_contents(
            $filename,
            $content
        );
    }

    public function createDir(string $filename)
    {
        mkdir($filename, 0755, TRUE);
    }

    public function dirExists(string $filename) : bool
    {
        return (is_dir($filename));
    }

    public function fileExists(string $filename) : bool
    {
        return file_exists($filename);
    }
}