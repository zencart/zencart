<?php

namespace Tests\Support\Traits;

use Tests\Support\Database\TestDb;

trait DiscountCouponConcerns
{

    protected array $couponProfiles = [
        'test10percent' => [
            'coupon' => [
                'coupon_type' => 'P',
                'coupon_code' => 'test10percent',
                'coupon_amount' => '10',
                'uses_per_user' => '5',
            ],
            'coupon_description' => [
                'coupon_name' => 'Test 10 Percent',
                'coupon_description' => 'Test 10 Percent',
                'language_id' => 1,
            ]
        ],
        'inactive10percent' => [
            'coupon' => [
                'coupon_type' => 'P',
                'coupon_code' => 'inactive10percent',
                'coupon_amount' => '10',
                'uses_per_user' => '5',
                'coupon_active' => 'N',
            ],
            'coupon_description' => [
                'coupon_name' => 'Inactive 10 Percent',
                'coupon_description' => 'Inactive 10 Percent',
                'language_id' => 1,
            ]
        ]
    ];

    public function createCoupon($profileName): int
    {
        if (!isset($this->couponProfiles[$profileName])) {
            return 0;
        }
        $profile = $this->couponProfiles[$profileName];
        $coupon = $profile['coupon'];
        $this->deleteExistingCoupon($coupon['coupon_code']);
        $coupon['coupon_start_date'] = date('Y-m-d H:i:s', strtotime('-5 days'));
        $coupon['coupon_expire_date'] = date('Y-m-d H:i:s', strtotime('+5 days'));
        $couponId = TestDb::insert('coupons', $coupon);

        $couponDescription = $profile['coupon_description'];
        $couponDescription['coupon_id'] = $couponId;
        TestDb::insert('coupons_description', $couponDescription);

        return (int) $couponId;
    }

    protected function deleteExistingCoupon(string $couponCode): void
    {
        $couponIds = TestDb::pdo()
            ->prepare('SELECT coupon_id FROM coupons WHERE coupon_code = :coupon_code');
        $couponIds->bindValue(':coupon_code', $couponCode);
        $couponIds->execute();
        $ids = array_map('intval', $couponIds->fetchAll(\PDO::FETCH_COLUMN));

        if ($ids === []) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        foreach (['coupon_restrict', 'coupons_description', 'coupons'] as $table) {
            $statement = TestDb::pdo()->prepare('DELETE FROM ' . $table . ' WHERE coupon_id IN (' . $placeholders . ')');
            foreach ($ids as $index => $id) {
                $statement->bindValue($index + 1, $id, \PDO::PARAM_INT);
            }
            $statement->execute();
        }
    }
}
