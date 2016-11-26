<?php

/**
 * Created by PhpStorm.
 * User: sebi
 * Date: 24.11.16
 * Time: 15:00
 */
interface IFileStorage
{

    public function readFile(string $filename) : string ;
    public function writeFile(string $filename, string $content);
    //public function attachFile(string $filename, string $content);
    public function fileExists(string $filename) : bool;

    public function dirExists(string  $filename) : bool ;
    public function createDir(string  $filename);


}