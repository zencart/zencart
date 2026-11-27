<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\UnitTestBootstrap;
use Zencart\FileSystem\FileSystem;
use Zencart\Templates\TemplateChild;

/**
 * A child template mirrors its parent's directory layout, renaming only the directory
 * named after the parent template. The rename has to act on whole path segments: a
 * sibling directory whose name merely begins with the parent's name is not part of the
 * template and must be left alone, and a directory nested inside the template that
 * happens to share the template's name keeps its own name.
 *
 * These tests drive the real FileSystem against a fixture tree rather than restating
 * the matching rule, so the shape getSubdirectoriesRecursive() actually returns is
 * part of what is covered.
 *
 * @see \Zencart\Templates\TemplateChild::createChildTemplateDirectories()
 * @see \Zencart\FileSystem\FileSystem::getSubdirectoriesRecursive()
 */
class TemplateChildDirectoryRewriteTest extends TestCase
{
    private const PARENT_DIR = 'classic';
    private const CHILD_DIR = 'mychild';

    private string $workDir;
    private string $parentRoot;
    private string $childRoot;

    public static function setUpBeforeClass(): void
    {
        /**
         * TemplateChild.php refuses to load unless IS_ADMIN_FLAG is true, and
         * UnitTestBootstrap defines it as false when it is still missing, so the
         * admin value has to be established first.
         */
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', true);
        }
        UnitTestBootstrap::initialize();
        require_once DIR_FS_CATALOG . 'includes/classes/FileSystem.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateChild.php';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->workDir = sys_get_temp_dir() . '/zc-child-dirs-' . bin2hex(random_bytes(6));
        $this->parentRoot = $this->workDir . '/parent/catalog/includes';
        $this->childRoot = $this->workDir . '/child/catalog/includes';

        foreach ($this->parentFixtureDirectories() as $relative) {
            mkdir($this->parentRoot . $relative, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->workDir);
        parent::tearDown();
    }

    /**
     * The parent plugin's layout, including the two shapes a substring rename gets wrong.
     */
    private function parentFixtureDirectories(): array
    {
        $p = self::PARENT_DIR;

        return [
            '/templates/' . $p,
            '/templates/' . $p . '/images',
            '/templates/' . $p . '/css',
            // A directory nested inside the template that shares the template's name.
            '/templates/' . $p . '/' . $p,
            // A sibling of the template whose name merely starts with the template's name.
            '/templates/' . $p . '_extras',
            '/languages/english/' . $p,
            '/modules/sideboxes/' . $p,
            // Nothing to do with the template; must not be mirrored.
            '/classes/Unrelated',
        ];
    }

    public function testChildTreeRenamesOnlyWholeTemplateDirectorySegments(): void
    {
        $this->assertTrue($this->createChildDirectories(), 'Creating the child directory tree should succeed.');

        $c = self::CHILD_DIR;
        $p = self::PARENT_DIR;

        $expected = [
            '/templates/' . $c,
            '/templates/' . $c . '/images',
            '/templates/' . $c . '/css',
            // The nested directory keeps its own name; only the template segment is renamed.
            '/templates/' . $c . '/' . $p,
            '/languages/english/' . $c,
            '/modules/sideboxes/' . $c,
        ];

        foreach ($expected as $relative) {
            $this->assertDirectoryExists(
                $this->childRoot . $relative,
                'Expected the child tree to contain ' . $relative
            );
        }
    }

    public function testSiblingDirectoryWithTemplateNameAsPrefixIsNotRenamed(): void
    {
        $this->createChildDirectories();

        $this->assertDirectoryDoesNotExist(
            $this->childRoot . '/templates/' . self::CHILD_DIR . '_extras',
            'A sibling directory whose name merely begins with the parent template name must not be renamed into the child.'
        );
    }

    public function testDirectoriesOutsideTheTemplateAreNotMirrored(): void
    {
        $this->createChildDirectories();

        $this->assertDirectoryDoesNotExist(
            $this->childRoot . '/classes/Unrelated',
            'Only directories belonging to the parent template are mirrored into the child.'
        );
    }

    /**
     * The parent's own directory name must never survive at the template segment, or the
     * child would write into a path that reads as the parent's.
     */
    public function testParentTemplateSegmentDoesNotSurviveInTheChildTree(): void
    {
        $this->createChildDirectories();

        $this->assertDirectoryDoesNotExist(
            $this->childRoot . '/templates/' . self::PARENT_DIR,
            'The child tree must not contain a directory named after the parent template.'
        );
        $this->assertDirectoryDoesNotExist(
            $this->childRoot . '/languages/english/' . self::PARENT_DIR,
            'The child tree must not contain a language directory named after the parent template.'
        );
    }

    /**
     * Pins the contract createChildTemplateDirectories() relies on: relative results are
     * rooted with a separator, carry no trailing separator, and are not prefixed with the
     * scanned root.
     */
    public function testRelativeSubdirectoryListingShapeIsStable(): void
    {
        $subdirectories = (new FileSystem())->getSubdirectoriesRecursive($this->parentRoot, true);

        $this->assertNotEmpty($subdirectories);

        foreach ($subdirectories as $subdirectory) {
            $this->assertStringStartsWith('/', $subdirectory, 'Relative results are rooted with a separator.');
            $this->assertStringNotContainsString(
                $this->parentRoot,
                $subdirectory,
                'Relative results must not retain the scanned root.'
            );
            $this->assertSame(
                rtrim($subdirectory, '/\\'),
                $subdirectory,
                'Relative results must not end with a directory separator.'
            );
        }

        $this->assertContains('/templates/' . self::PARENT_DIR, $subdirectories);
        $this->assertContains('/templates/' . self::PARENT_DIR . '/' . self::PARENT_DIR, $subdirectories);
    }

    private function createChildDirectories(): bool
    {
        $child = (new ReflectionClass(TemplateChild::class))->newInstanceWithoutConstructor();

        $this->setPrivate($child, 'parentTemplateDirectory', self::PARENT_DIR);
        $this->setPrivate($child, 'templateDirectory', self::CHILD_DIR);
        $this->setPrivate($child, 'fs', new FileSystem());

        $method = new ReflectionMethod($child, 'createChildTemplateDirectories');

        return (bool)$method->invoke($child, $this->parentRoot, $this->childRoot);
    }

    private function setPrivate(object $object, string $property, mixed $value): void
    {
        $reflected = new \ReflectionProperty($object, $property);
        $reflected->setValue($object, $value);
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $entry) {
            $entry->isDir() ? @rmdir($entry->getPathname()) : @unlink($entry->getPathname());
        }

        @rmdir($directory);
    }
}
