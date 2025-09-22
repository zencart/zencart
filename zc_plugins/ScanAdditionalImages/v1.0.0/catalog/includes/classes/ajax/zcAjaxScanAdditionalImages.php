<?php

zen_define_default('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG . 'images/');

class zcAjaxScanAdditionalImages
{
    public function doBatch(): array
    {
        global $db;

        $start_at = 0;
        $batch_size = 10;

        if (is_numeric($_POST['start_at']) && (int)$_POST['start_at'] >= 0) {
            $start_at = (int)$_POST['start_at'];
        }

        // Note: batch size of zero is a utility call to return available count only
        if (is_numeric($_POST['batch_size']) && (int)$_POST['batch_size'] >= 0) {
            $batch_size = (int)$_POST['batch_size'];
        }
        // Limit batch processing (if over 100, reset to 10 for better progress-meter experience)
        if ($batch_size > 100) {
            $batch_size = 10;
        }

        $time_limit = 600; // 10 minutes
        if ($batch_size > 0) {
            $time_limit = $batch_size * 10; // 10 seconds per product
        }
        zen_set_time_limit($time_limit);

        $sql = $this->buildQuery($start_at, $batch_size);
        $products_query = $db->Execute($sql);

        // If no products found, return error
        if ($products_query->EOF) {
            return [
                'batchRecordsFound' => 0,
                'errorMessage' => TEXT_NOTHING_TO_PROCESS,
                'imagesInserted' => 0,
                'next_batch' => 0,
                'next_start' => 0,
                'recordsProcessed' => 0,
                'remaining' => 0,
                'this_batch_size' => 0,
                'this_start_at' => 0,
                'terminate' => true,
            ];
        }

        $batchRecordsFound = (int)$products_query->RecordCount();

        // Utility call: when chunk_size is zero, return available count only
        if (empty($batch_size)) {
            return [
                'batchRecordsFound' => $batchRecordsFound,
                'errorMessage' => TEXT_QUERY_ONLY,
                'imagesInserted' => 0,
                'next_batch' => 10,
                'next_start' => 1,
                'recordsProcessed' => 0,
                'remaining' => 0,
                'this_batch_size' => $batch_size,
                'this_start_at' => $start_at,
                'terminate' => true,
            ];
        }

        // Process products in this batch
        [$inserted, $counter] = $this->processProducts($products_query);

        // Check to see if more products remain
        $sql = $this->buildQuery($start_at + $counter, PHP_INT_MAX, $count_only = true);
        $products_query = $db->Execute($sql);
        $remaining = $products_query->EOF ? 0 : (int)$products_query->fields['remaining_rows'];
        if ($remaining === 0) {
            // Set abort flag if none left
            $terminate = false;
        }
        // Set next batch count (may be less than requested if fewer remain)
        $nextBatchCount = min($remaining, $batch_size);

        return [
            'batchRecordsFound' => $batchRecordsFound,
            'errorMessage' => '',
            'imagesInserted' => $inserted,
            'next_batch' => $nextBatchCount,
            'next_start' => $start_at + $counter,
            'recordsProcessed' => $counter,
            'remaining' => $remaining,
            'this_batch_size' => $batch_size,
            'this_start_at' => $start_at,
            'terminate' => $terminate ?? false,
        ];
    }

    /**
     * @param queryFactoryResult $products_query
     * @return array
     */
    protected function processProducts(queryFactoryResult $products_query): array
    {
        global $db;
        $counter = $inserted = 0;

        foreach ($products_query as $product) {
            $products_id = (int)$product['products_id'];
            $products_image = $product['products_image'];

            // Get base filename without extension
            $image_extension = substr($products_image, strrpos($products_image, '.'));
            $image_base = basename($products_image, $image_extension);

            // Detect subdirectory
            $subdir = '';
            if (strpos($products_image, '/') !== false) {
                $subdir = substr($products_image, 0, strrpos($products_image, '/') + 1);
            }
            $image_dir = DIR_FS_CATALOG_IMAGES . $subdir;

            // Use '_' suffix unless legacy mode
            if (defined('ADDITIONAL_IMAGES_MODE') && ADDITIONAL_IMAGES_MODE !== 'legacy' && !str_ends_with($image_base, '_')) {
                $image_base .= '_';
            }

            $matches = [];
            // Scan directory for matching files using glob iterator, which sorts alphabetically (so sort_order is retained)
            $images = zen_get_files_in_directory($image_dir, $image_extension);
            foreach ($images as $file) {
                $file = preg_replace('/^' . preg_quote($image_dir, '/') . '/i', '', $file);
                if (!is_dir($image_dir . $file)) {
                    if (preg_match('/' . preg_quote($image_base, '/') . '/i', $file) === 1 && $file !== $products_image) {
                        $matches[] = $file;
                    }
                }
            }

            // This loop performs many filesystem stat operations, which may overload PHP's cache. So we clean it up here.
            // https://www.php.net/manual/en/function.clearstatcache.php
            clearstatcache();

            // Insert matches into products_additional_images table
            foreach ($matches as $sort_order => $additional_image) {
                // Check if already exists
                $exists_query = $db->Execute(
                    "SELECT id FROM " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " WHERE products_id = $products_id AND additional_image = '" . zen_db_input($subdir . $additional_image) . "'"
                );
                if ($exists_query->EOF) {
                    $result = $db->Execute(
                        "INSERT INTO " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " (products_id, additional_image, sort_order)
                        VALUES ($products_id, '" . zen_db_input($subdir . $additional_image) . "', " . (int)$sort_order . ")"
                    );
                    $inserted += mysqli_affected_rows($result->link);
                }
            }

            $counter++;
        }
        return [$inserted, $counter];
    }

    protected function buildQuery(int $start_at, int $batch_size, $count_only = false): string
    {
        // Base query to fetch products with images
        $sql = "SELECT products_id, products_image";
        if ($count_only && $batch_size > 0) {
            $sql = "SELECT COUNT(*) AS remaining_rows FROM (SELECT 1 ";
        }

        $sql .= " FROM " . TABLE_PRODUCTS . "
                WHERE products_image IS NOT NULL
                AND products_image != '" . zen_db_input(PRODUCTS_IMAGE_NO_IMAGE) . "'";

        if ($batch_size < 1) {
            return $sql;
        }

        $sql .= " LIMIT " . $batch_size;

        // add starting offset (start_at)
        if ($start_at > 1) {
            $sql .= " OFFSET " . $start_at;
        }

        if ($count_only) {
            $sql .= ") AS temporary_count_table";
        }

        return $sql;
    }
}
