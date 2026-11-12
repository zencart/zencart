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

    /**
     * The stable, language-independent part of TEXT_NO_STATUS_HISTORY_FOR_ORDER.
     */
    private const NO_HISTORY_TEXT = 'No status-history record exists for this order';

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

    /**
     * An order carrying a status but no orders_status_history rows at all is a
     * worse desync than a stale newest record, since every order gets a history
     * record when it is created. The no-history branch must say so, and must use
     * its own wording rather than the stale-record branch's.
     */
    public function testNoHistoryWarningShownWhenOrderHasNoStatusHistoryAtAll(): void
    {
        $order = $this->completeSimpleStorefrontCheckout('florida-basic1');

        $this->completeInitialAdminSetup();

        $orderStatus = (int) TestDb::selectValue(
            'SELECT orders_status FROM orders WHERE orders_id = :order_id LIMIT 1',
            [':order_id' => $order['order_id']]
        );
        $statusName = TestDb::selectValue(
            'SELECT orders_status_name
               FROM orders_status
              WHERE orders_status_id = :status
                AND language_id = 1
              LIMIT 1',
            [':status' => $orderStatus]
        );

        // Strip the history entirely, leaving orders.orders_status in place.
        $deleted = TestDb::pdo()->prepare('DELETE FROM orders_status_history WHERE orders_id = :order_id');
        $deleted->execute([':order_id' => $order['order_id']]);
        $this->assertGreaterThan(0, $deleted->rowCount(), 'Expected the order to have had history rows to delete.');

        $this->assertNull(
            TestDb::selectOne(
                'SELECT orders_status_history_id FROM orders_status_history WHERE orders_id = :order_id LIMIT 1',
                [':order_id' => $order['order_id']]
            ),
            'Precondition failed: the order still has status-history rows.'
        );

        $page = $this->getAdmin('/admin/index.php?cmd=orders&oID=' . $order['order_id'] . '&action=edit')
            ->assertOk()
            ->assertSee(self::NO_HISTORY_TEXT)
            ->assertSee($statusName . ' [' . $orderStatus . ']');

        // The two conditions have separate messages; this branch must not emit the other one.
        $this->assertStringNotContainsString(
            self::MISMATCH_TEXT,
            $page->content,
            'The no-history branch must use its own wording, not the stale-record mismatch text.'
        );
    }
}
