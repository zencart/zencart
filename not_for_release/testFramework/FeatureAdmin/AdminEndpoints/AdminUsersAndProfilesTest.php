<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * @group parallel-candidate
 */
class AdminUsersAndProfilesTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testAdminCanCreateAndRenameProfile(): void
    {
        $this->completeInitialAdminSetup();

        $page = $this->getAdmin('/admin/index.php?cmd=profiles&action=add')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('New Profile for');

        $response = $this->submitAdminForm($page, 'profiles', [
            'action' => 'insert',
            'name' => 'Test Profile',
            'p' => ['users'],
        ]);

        $response->assertOk()
            ->assertSee('Test Profile');

        $profileId = TestDb::selectValue(
            'SELECT profile_id FROM admin_profiles WHERE profile_name = :name LIMIT 1',
            [':name' => 'Test Profile']
        );

        $this->assertNotNull($profileId);

        $renamePage = $this->getAdmin('/admin/index.php?cmd=profiles&action=rename&profile=' . $profileId)
            ->assertOk()
            ->assertSee('Test Profile');

        $renameResponse = $this->submitAdminForm($renamePage, 'profileNameForm', [
            'action' => 'update_name',
            'profile' => $profileId,
            'profile-name' => 'Renamed Profile',
        ]);

        $renameResponse->assertOk()
            ->assertSee('User Profiles')
            ->assertSee('Renamed Profile');

        $renamedProfile = TestDb::selectValue(
            'SELECT profile_name FROM admin_profiles WHERE profile_id = :profile_id LIMIT 1',
            [':profile_id' => $profileId]
        );

        $this->assertSame('Renamed Profile', (string) $renamedProfile);
    }

    public function testAdminCanCreateAndUpdateAnotherAdminUser(): void
    {
        $this->completeInitialAdminSetup();

        TestDb::insert('admin_profiles', [
            'profile_name' => 'Users Test Profile',
        ]);

        $profileId = TestDb::selectValue(
            'SELECT profile_id FROM admin_profiles WHERE profile_name = :name LIMIT 1',
            [':name' => 'Users Test Profile']
        );

        $this->assertNotNull($profileId);

        TestDb::insert('admin_pages_to_profiles', [
            'page_key' => 'users',
            'profile_id' => $profileId,
        ]);

        $page = $this->getAdmin('/admin/index.php?cmd=users&action=add')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'admin')
            ->assertSee('Admin Users');

        $response = $this->submitAdminForm($page, 'users', [
            'action' => 'insert',
            'name' => 'TestUser',
            'email' => 'testuser@example.com',
            'profile' => $profileId,
            'password' => 'Adminpass123',
            'confirm' => 'Adminpass123',
        ]);

        $response->assertOk()
            ->assertSee('Admin Users')
            ->assertSee('TestUser')
            ->assertSee('testuser@example.com');

        $userId = TestDb::selectValue(
            'SELECT admin_id FROM admin WHERE admin_name = :name LIMIT 1',
            [':name' => 'TestUser']
        );

        $this->assertNotNull($userId);

        $editPage = $this->getAdmin('/admin/index.php?cmd=users&action=edit&user=' . $userId)
            ->assertOk()
            ->assertSee('TestUser');

        $editResponse = $this->submitAdminForm($editPage, 'users', [
            'action' => 'update',
            'user' => $userId,
            'id' => $userId,
            'name' => 'TestUserRenamed',
            'email' => 'renamed-admin@example.com',
            'profile' => $profileId,
        ]);

        $editResponse->assertOk()
            ->assertSee('Admin Users')
            ->assertSee('TestUserRenamed');

        $updatedUser = TestDb::selectOne(
            'SELECT admin_name, admin_email, admin_profile FROM admin WHERE admin_id = :admin_id LIMIT 1',
            [':admin_id' => $userId]
        );

        $this->assertNotNull($updatedUser);
        $this->assertSame('TestUserRenamed', $updatedUser['admin_name']);
        $this->assertSame('renamed-admin@example.com', $updatedUser['admin_email']);
        $this->assertSame((string) $profileId, (string) $updatedUser['admin_profile']);
    }

    protected function completeInitialAdminSetup(): void
    {
        $this->visitAdminHome()
            ->assertOk()
            ->assertSee('Admin Login');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Initial Setup Wizard');

        $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
        ])->assertOk()
            ->assertSee('Initial Setup Wizard');

        $this->submitAdminSetupWizard([
            'store_name' => 'Zencart Store',
            'store_owner' => 'Store Owner',
        ])->assertOk()
            ->assertSee('Admin Home');
    }
}
