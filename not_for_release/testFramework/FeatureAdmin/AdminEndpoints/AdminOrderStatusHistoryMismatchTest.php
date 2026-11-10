<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\Traits\InProcessStorefrontCheckoutConcerns;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
class AdminOrderStatusHistoryMismatchTest extends zcInProcessFeatureTestCaseAdmin
{
    use CustomerAccountConcerns;
    use InProcessStorefrontCheckoutConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    /**
     * The stable, language-independent part of TEXT_STATUS_HISTORY_MISMATCH.
     */
    private const MISMATCH_TEXT = 'Mismatch with current order status';

    public function setUp(): void
    {
        parent::setUp();
        $this->resetStorefrontSession();
    }

    /**
     * A freshly-placed order has orders.orders_status and its newest
     * orders_status_history row in agreement, so the warning must not appear.
     * This is the regression guard: a false positive here would put a red
     * alert on every order in the admin.
     */
    public function testNoMismatchWarningWhenOrderStatusAgreesWithHistory(): void
    {
        $order = $this->completeSimpleStorefrontCheckout('florida-basic1');

        $this->completeInitialAdminSetup();

        $orderStatus = (int) TestDb::selectValue(
            'SELECT orders_status FROM orders WHERE orders_id = :order_id LIMIT 1',
            [':order_id' => $order['order_id']]
        );
        $latestHistoryStatus = (int) TestDb::selectValue(
            'SELECT orders_status_id
               FROM orders_status_history
              WHERE orders_id = :order_id
              ORDER BY date_added DESC, orders_status_history_id DESC
              LIMIT 1',
            [':order_id' => $order['order_id']]
        );

        // Precondition: the fixture really is in the in-sync state we are asserting about.
        $this->assertSame(
            $orderStatus,
            $latestHistoryStatus,
            'Fixture precondition failed: a new order should have matching order and history statuses.'
        );

        $page = $this->getAdmin('/admin/index.php?cmd=orders&oID=' . $order['order_id'] . '&action=edit')
            ->assertOk()
            ->assertSee((string) $order['order_id']);

        $this->assertStringNotContainsString(
            self::MISMATCH_TEXT,
            $page->content,
            'An in-sync order must not display the status-history mismatch warning.'
        );
    }

    /**
     * Writing orders.orders_status without a matching history record is the
     * desync this warning exists to surface, so the order-details page must
     * flag it and name the order's authoritative status.
     */
    public function testMismatchWarningShownWhenOrderStatusDivergesFromHistory(): void
    {
        $order = $this->completeSimpleStorefrontCheckout('florida-basic1');

        $this->completeInitialAdminSetup();

        $latestHistoryStatus = (int) TestDb::selectValue(
            'SELECT orders_status_id
               FROM orders_status_history
              WHERE orders_id = :order_id
              ORDER BY date_added DESC, orders_status_history_id DESC
              LIMIT 1',
            [':order_id' => $order['order_id']]
        );

        // Any other defined status will do; pick one so the test does not depend on specific ids.
        $divergentStatus = TestDb::selectOne(
            'SELECT orders_status_id, orders_status_name
               FROM orders_status
              WHERE language_id = 1
                AND orders_status_id <> :current
              ORDER BY orders_status_id
              LIMIT 1',
            [':current' => $latestHistoryStatus]
        );
        $this->assertNotNull($divergentStatus, 'Expected at least two defined order statuses.');

        // Deliberately update the order without writing an orders_status_history record.
        TestDb::update(
            'orders',
            ['orders_status' => (int) $divergentStatus['orders_status_id']],
            'orders_id = :order_id',
            [':order_id' => $order['order_id']]
        );

        $unchangedHistoryStatus = (int) TestDb::selectValue(
            'SELECT orders_status_id
               FROM orders_status_history
              WHERE orders_id = :order_id
              ORDER BY date_added DESC, orders_status_history_id DESC
              LIMIT 1',
            [':order_id' => $order['order_id']]
        );
        $this->assertSame(
            $latestHistoryStatus,
            $unchangedHistoryStatus,
            'The status history must be left untouched for this to be a mismatch.'
        );

        $page = $this->getAdmin('/admin/index.php?cmd=orders&oID=' . $order['order_id'] . '&action=edit')
            ->assertOk()
            ->assertSee(self::MISMATCH_TEXT);

        // The warning names the order's status (zen_getOrdersStatuses() renders these as "Name [id]").
        $page->assertSee(
            $divergentStatus['orders_status_name'] . ' [' . $divergentStatus['orders_status_id'] . ']'
        );
    }
}
