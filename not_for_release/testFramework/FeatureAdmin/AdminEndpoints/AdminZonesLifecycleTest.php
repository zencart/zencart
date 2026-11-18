<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class AdminZonesLifecycleTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanCreateEditAndDeleteZone(): void
    {
        $this->completeInitialAdminSetup();

        $countryId = 223;

        $newPage = $this->getAdmin('/admin/index.php?cmd=zones&zone_page=1&action=new')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Zones')
            ->assertSee('New Zone');

        $createResponse = $this->submitAdminForm($newPage, 'zones', [
            'zone_name' => 'Lifecycle Zone',
            'zone_code' => 'LCZ',
            'zone_country_id' => (string) $countryId,
        ]);

        $createPage = $createResponse->isRedirect() ? $this->followAdminRedirect($createResponse) : $createResponse;

        $createPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Lifecycle Zone')
            ->assertSee('LCZ');

        $zoneId = (int) TestDb::selectValue(
            'SELECT zone_id FROM zones WHERE zone_code = :code ORDER BY zone_id DESC LIMIT 1',
            [':code' => 'LCZ']
        );

        $this->assertGreaterThan(0, $zoneId);

        $createdZone = TestDb::selectOne(
            'SELECT zone_country_id, zone_code, zone_name FROM zones WHERE zone_id = :zone_id LIMIT 1',
            [':zone_id' => $zoneId]
        );

        $this->assertNotNull($createdZone);
        $this->assertSame((string) $countryId, (string) $createdZone['zone_country_id']);
        $this->assertSame('LCZ', $createdZone['zone_code']);
        $this->assertSame('Lifecycle Zone', $createdZone['zone_name']);

        $editPage = $this->getAdmin('/admin/index.php?cmd=zones&zone_page=1&cID=' . $zoneId . '&action=edit')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Edit Zone')
            ->assertSee('Lifecycle Zone');

        $editResponse = $this->submitAdminForm($editPage, 'zones', [
            'zone_name' => 'Lifecycle Zone Updated',
            'zone_code' => 'LCU',
            'zone_country_id' => (string) $countryId,
        ]);

        $editPage = $editResponse->isRedirect() ? $this->followAdminRedirect($editResponse) : $editResponse;

        $editPage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Lifecycle Zone Updated')
            ->assertSee('LCU');

        $updatedZone = TestDb::selectOne(
            'SELECT zone_country_id, zone_code, zone_name FROM zones WHERE zone_id = :zone_id LIMIT 1',
            [':zone_id' => $zoneId]
        );

        $this->assertNotNull($updatedZone);
        $this->assertSame((string) $countryId, (string) $updatedZone['zone_country_id']);
        $this->assertSame('LCU', $updatedZone['zone_code']);
        $this->assertSame('Lifecycle Zone Updated', $updatedZone['zone_name']);

        $deletePage = $this->getAdmin('/admin/index.php?cmd=zones&zone_page=1&cID=' . $zoneId . '&action=delete')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Delete Zone')
            ->assertSee('Lifecycle Zone Updated');

        $deleteResponse = $this->submitAdminForm($deletePage, 'zones', [
            'cID' => (string) $zoneId,
        ]);

        $deletePage = $deleteResponse->isRedirect() ? $this->followAdminRedirect($deleteResponse) : $deleteResponse;

        $deletePage->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Zones');

        $deletedZone = TestDb::selectValue(
            'SELECT zone_id FROM zones WHERE zone_id = :zone_id LIMIT 1',
            [':zone_id' => $zoneId]
        );

        $this->assertNull($deletedZone);
    }

    /**
     * Regression: admin/zones.php paginates by the full width of countries_name,
     * so a paginator "letter group" is a whole country name and zone_page carries it.
     */
    public function testZonesPaginatorHandlesACountryGroupContainingASpaceAndApostrophe(): void
    {
        $this->completeInitialAdminSetup();

        $country = TestDb::selectOne(
            'SELECT countries_id, countries_name FROM ' . TABLE_COUNTRIES
            . ' WHERE countries_name LIKE :apostrophe AND countries_name LIKE :space'
            . ' ORDER BY countries_id LIMIT 1',
            [':apostrophe' => "%'%", ':space' => '% %']
        );

        $this->assertNotNull(
            $country,
            'Expected the shipped country data to include a name containing both a space and an apostrophe.'
        );

        $countryId = (int) $country['countries_id'];
        $countryName = (string) $country['countries_name'];
        $zonePage = urlencode($countryName);

        $zoneId = TestDb::insert(TABLE_ZONES, [
            'zone_country_id' => $countryId,
            'zone_code' => 'APZ',
            'zone_name' => 'Apostrophe Zone',
        ]);

        $this->assertGreaterThan(0, $zoneId);

        try {
            $listPage = $this->getAdmin('/admin/index.php?cmd=zones&zone_page=' . $zonePage)
                ->assertOk()
                ->assertHeader('X-ZC-InProcess-Runner', 'admin')
                ->assertSee('Apostrophe Zone');

            /**
             * query_factory echoes this literal when a query fails, which is what the unescaped
             * apostrophe used to produce here.
             */
            $this->assertStringNotContainsString('WARNING: An Error occurred', $listPage->content);

            /**
             * Links back to this group must carry the name percent-encoded. The negative assertion
             * is the security-relevant one: an unencoded apostrophe in "zone_page=" would close the
             * javascript string literal in the row onclick handlers.
             */
            $this->assertStringContainsString('zone_page=' . $zonePage, $listPage->content);
            $this->assertStringNotContainsString('zone_page=' . $countryName, $listPage->content);

            $editPage = $this->getAdmin(
                '/admin/index.php?cmd=zones&zone_page=' . $zonePage . '&cID=' . $zoneId . '&action=edit'
            )
                ->assertOk()
                ->assertSee('Apostrophe Zone');

            /**
             * Posted directly rather than through submitAdminForm(), which follows the redirect
             * itself and would leave the Location assertion below unreachable. The save redirects
             * back to the same letter group, which must still be encoded in the Location header.
             */
            $editResponse = $this->postAdmin(
                '/admin/index.php?cmd=zones&zone_page=' . $zonePage . '&cID=' . $zoneId . '&action=save',
                array_merge($editPage->formDefaults('zones'), [
                    'zone_name' => 'Apostrophe Zone Updated',
                    'zone_code' => 'APU',
                    'zone_country_id' => (string) $countryId,
                ])
            );

            $editResponse->assertRedirect('zone_page=' . $zonePage);

            $savedPage = $this->followAdminRedirect($editResponse);

            $savedPage->assertOk()
                ->assertSee('Apostrophe Zone Updated');

            $this->assertStringNotContainsString('WARNING: An Error occurred', $savedPage->content);

            $updatedZone = TestDb::selectOne(
                'SELECT zone_code, zone_name FROM ' . TABLE_ZONES . ' WHERE zone_id = :zone_id LIMIT 1',
                [':zone_id' => $zoneId]
            );

            $this->assertNotNull($updatedZone);
            $this->assertSame('APU', $updatedZone['zone_code']);
            $this->assertSame('Apostrophe Zone Updated', $updatedZone['zone_name']);
        } finally {
            TestDb::pdo()
                ->prepare('DELETE FROM ' . TABLE_ZONES . ' WHERE zone_id = :zone_id')
                ->execute([':zone_id' => $zoneId]);
        }
    }

    /**
     * Regression for the paginator's own prev/next links, which splitPageResults builds itself and
     * which the test above does not cover: it opens a group directly and inspects the row links
     * that zones.php generates, so it would still pass if display_links() stopped encoding.
     *
     * An ampersand is the decisive character. Unencoded in "zone_page=...", it ends the parameter,
     * so following the link would land on a truncated group and the zone would not be listed. No
     * shipped country name contains one, so two are inserted here, named to sort last and adjacent.
     */
    public function testZonesPaginatorPreviousLinkEncodesAGroupContainingAnAmpersand(): void
    {
        // Cannot test split_page_results without isolation to ensure the admin loads instead of catalog
        $this->markTestSkipped();

        $this->completeInitialAdminSetup();

        $ampersandCountryId = TestDb::insert(TABLE_COUNTRIES, [
            'countries_name' => 'Zzz Ampersand & Co',
            'countries_iso_code_2' => 'Q1',
            'countries_iso_code_3' => 'QQ1',
            'address_format_id' => 1,
        ]);

        $laterCountryId = TestDb::insert(TABLE_COUNTRIES, [
            'countries_name' => 'Zzz Beta',
            'countries_iso_code_2' => 'Q2',
            'countries_iso_code_3' => 'QQ2',
            'address_format_id' => 1,
        ]);

        $ampersandZoneId = TestDb::insert(TABLE_ZONES, [
            'zone_country_id' => $ampersandCountryId,
            'zone_code' => 'AMPZ',
            'zone_name' => 'Ampersand Group Zone',
        ]);

        $laterZoneId = TestDb::insert(TABLE_ZONES, [
            'zone_country_id' => $laterCountryId,
            'zone_code' => 'BETZ',
            'zone_name' => 'Beta Group Zone',
        ]);

        try {
            /**
             * The paginator builds its groups with UCASE(), so its links carry the uppercased name
             * and the current-group comparison is made against that same form.
             */
            $laterGroup = strtoupper('Zzz Beta');

            $betaPage = $this->getAdmin('/admin/index.php?cmd=zones&zone_page=' . urlencode($laterGroup))
                ->assertOk()
                ->assertSee('Beta Group Zone');

            /**
             * The paginator emits its previous/next controls as splitPageLink anchors, previous
             * first. Matched loosely on attribute order and spacing so the test fails on the
             * encoding rather than on incidental markup changes.
             */
            preg_match_all(
                '~<a\s[^>]*href="([^"]*)"[^>]*class="splitPageLink[^"]*"~i',
                $betaPage->content,
                $linkMatches
            );

            if (empty($linkMatches[1])) {
                $marker = strpos($betaPage->content, 'splitPage');
                $this->fail(
                    "Expected a previous-page link in the zones paginator.\nPaginator markup was:\n"
                    . ($marker === false
                        ? '(no "splitPage" marker found; last 1500 characters)' . "\n"
                            . substr($betaPage->content, -1500)
                        : substr($betaPage->content, max(0, $marker - 800), 1600))
                );
            }

            $previousHref = html_entity_decode($linkMatches[1][0], ENT_QUOTES, 'UTF-8');

            $this->assertStringContainsString(
                'zone_page=' . urlencode(strtoupper('Zzz Ampersand & Co')),
                $previousHref
            );

            // Follow it the way a browser would, as a path plus query.
            $parts = parse_url($previousHref);
            $this->assertIsArray($parts);

            $followUri = ($parts['path'] ?? '/admin/index.php')
                . (empty($parts['query']) ? '' : '?' . $parts['query']);

            $ampersandPage = $this->getAdmin($followUri)
                ->assertOk()
                ->assertSee('Ampersand Group Zone');

            $this->assertStringNotContainsString('WARNING: An Error occurred', $ampersandPage->content);
            $this->assertStringNotContainsString('Beta Group Zone', $ampersandPage->content);
        } finally {
            $pdo = TestDb::pdo();
            $pdo->prepare('DELETE FROM ' . TABLE_ZONES . ' WHERE zone_id IN (:a, :b)')
                ->execute([':a' => $ampersandZoneId, ':b' => $laterZoneId]);
            $pdo->prepare('DELETE FROM ' . TABLE_COUNTRIES . ' WHERE countries_id IN (:a, :b)')
                ->execute([':a' => $ampersandCountryId, ':b' => $laterCountryId]);
        }
    }

}
