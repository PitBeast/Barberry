<?php
namespace Barberry;
use Barberry\Test;

class CacheIntegrationTest extends \PHPUnit_Framework_TestCase {

    private $cache_path;

    protected function setUp() {
        $this->cache_path = '/tmp/testCache/';
        @mkdir($this->cache_path);
    }

    protected function tearDown() {
        exec('rm -rf ' . $this->cache_path);
    }

    public function testIsContentSavedInFileSystem() {
        $this->cache()->save(
            Test\Data::gif1x1(),
            new Request('/7yU98sd_1x1.gif')
        );

        $expectedPath = $this->cache_path . '/7y/U9/7yU98sd/7yU98sd_1x1.gif';

        $this->assertEquals(file_get_contents($expectedPath), Test\Data::gif1x1());
    }

    public function testIsContentSavedInFileSystemInGroupDirectory() {
        $this->cache()->save(
            Test\Data::gif1x1(),
            new Request('/adm/7yU98sd_1x1.gif')
        );

        $expectedPath = $this->cache_path.ltrim('/adm/7y/U9/7yU98sd/7yU98sd_1x1.gif');

        $this->assertEquals(file_get_contents($expectedPath), Test\Data::gif1x1());
    }

    public function testIsSpecificCacheDirectoryInvalidated() {
        $this->cache()->save(
            Test\Data::gif1x1(),
            new Request('/XyU98sd_1x1.gif')
        );

        $expectedPath = dirname($this->cache_path . '/Xy/U9/XyU98sd/XyU98sd_1x1.gif');
        $this->cache()->invalidate('XyU98sd');
        $this->assertEquals(file_exists($expectedPath), false);
    }

    public function testIsAllLevelEmptyCacheDirectoryInvalidated() {
        $this->cache()->save(
            Test\Data::gif1x1(),
            new Request('/XyU98sd_1x1.gif')
        );

        $this->cache()->save(
            Test\Data::gif1x1(),
            new Request('/XyU08se_1x1.gif')
        );

        $this->cache()->invalidate('XyU98sd');
        $expectedPath = dirname($this->cache_path . '/Xy/U9/XyU98sd/');
        $this->assertEquals(file_exists($expectedPath), false);
        $this->assertEquals(file_exists(dirname($expectedPath)), true);

        $this->cache()->invalidate('XyU08se');
        $this->assertEquals(file_exists(dirname($expectedPath)), false);
    }

//--------------------------------------------------------------------------------------------------

    private function cache() {
        return new Cache($this->cache_path);
    }

    private static function rmDirRecursive($dir) {
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') continue;
            if (!self::rmDirRecursive($dir . '/' . $file)) {
                chmod($dir . '/' . $file, 0777);
                if (!self::rmDirRecursive($dir . '/' . $file)) return false;
            };
        }
        return rmdir($dir);
    }
}