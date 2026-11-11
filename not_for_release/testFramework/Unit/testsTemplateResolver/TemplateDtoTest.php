<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;
use Zencart\Templates\TemplateDto;

#[RunTestsInSeparateProcesses]
class TemplateDtoTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
    }

    public function testUpdatingATemplateCreatesAndMergesItsRecord(): void
    {
        $dto = TemplateDto::getInstance();

        $created = $dto->updateTemplate('alpha', ['name' => 'Alpha', 'keep' => 'old']);
        $merged = $dto->updateTemplate('alpha', ['keep' => 'new', 'extra' => true]);

        $this->assertSame(['name' => 'Alpha', 'keep' => 'old'], $created);
        $this->assertSame(['name' => 'Alpha', 'keep' => 'new', 'extra' => true], $merged);
        $this->assertSame($merged, $dto->getTemplate('alpha'));
    }

    public function testRecordsAreKeptPerTemplateAndCanBeRemoved(): void
    {
        $dto = TemplateDto::getInstance();
        $dto->updateTemplate('alpha', ['n' => 1]);
        $dto->updateTemplate('beta', ['n' => 2]);

        $this->assertSame(['alpha' => ['n' => 1], 'beta' => ['n' => 2]], $dto->getAllTemplates());
        $this->assertNull($dto->getTemplate('gamma'));

        $dto->removeTemplate('alpha');

        $this->assertNull($dto->getTemplate('alpha'));
        $this->assertSame(['beta' => ['n' => 2]], $dto->getAllTemplates());
        $this->assertSame(TemplateDto::getInstance(), $dto);
    }
}
