<?php
namespace Barberry;

class Cache {

    private $path;

    public function __construct($path) {
        $this->path = rtrim($path,'/') . '/';
    }

    public function save($content, Request $request) {
        $this->writeToFilesystem($content, $this->filePath($request));
        $this->assertFileWasWritten($this->filePath($request));
    }

    public function link($path, Request $request) {
        $this->linkToFilesystem($path, $this->filePath($request));
        $this->assertFileWasWritten($this->filePath($request));
    }

    public function invalidate($id) {
        $dir = $this->path . substr($id, 0, 2) . '/' . substr($id, 2, 2) . '/' . $id;
        if(is_dir($dir)) {
            self::rmDirRecursive($dir);
        }
        if (self::isDirectoryEmpty(dirname($dir)) === true) {
            self::rmDirRecursive(dirname($dir));
        }
        if (self::isDirectoryEmpty(dirname(dirname($dir))) === true) {
            self::rmDirRecursive(dirname(dirname($dir)));
        }
    }

//--------------------------------------------------------------------------------------------------

    protected function writeToFilesystem($content, $filePath) {
        if(!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        file_put_contents($filePath, $content);
        chmod($filePath, 0777);
    }

    protected function linkToFilesystem($path, $filePath) {
        if(!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        link($path, $filePath);
        chmod($filePath, 0664);
    }

    private function assertFileWasWritten($filePath) {
        if (false == is_file($filePath)) {
            throw new Cache\Exception($filePath);
        }
    }

    private function filePath(Request $request) {
        return $this->path . self::idIsDirectoryName($request);
    }

    private static function idIsDirectoryName(Request $request) {
        return join(
            '/',
            array_filter(
                array(
                    $request->group,
                    substr($request->id, 0, 2) . '/' .
                    substr($request->id, 2, 2) . '/' .
                    $request->id . '/' . $request->originalBasename
                )
            )
        );
    }

    private static function rmDirRecursive($dir) {
        if (!is_dir($dir) || is_link($dir)) return @unlink($dir);
        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') continue;
            if (!self::rmDirRecursive($dir . '/' . $file)) {
                @chmod($dir . '/' . $file, 0777);
                if (!self::rmDirRecursive($dir . '/' . $file)) return false;
            };
        }
        return rmdir($dir);
    }

    private static function isDirectoryEmpty($dir) {
        if (!is_readable($dir)) return null;
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return false;
            }
        }
        return true;
    }

}
