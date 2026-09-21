<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Migrations;

use Override;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260101000200ShopifyBaseline extends Migration
{
    #[Override]
    public function safeUp(): void
    {
        $this->execute(
            <<<'SQL'
            CREATE TABLE `product` (
              `id` bigint(20) unsigned NOT NULL,
              `status` tinyint(1) unsigned NOT NULL DEFAULT 3,
              `variant_id` bigint(20) unsigned DEFAULT NULL,
              `image_id` bigint(20) unsigned DEFAULT NULL,
              `name` varchar(255) NOT NULL,
              `content` text DEFAULT NULL,
              `slug` varchar(255) NOT NULL,
              `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
              `vendor` varchar(255) DEFAULT NULL,
              `product_type` varchar(255) DEFAULT NULL,
              `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
              `total_inventory_quantity` int(11) NOT NULL DEFAULT 0,
              `image_count` smallint(6) unsigned NOT NULL DEFAULT 0,
              `variant_count` smallint(6) unsigned NOT NULL DEFAULT 0,
              `last_import_at` datetime NOT NULL,
              `updated_at` datetime DEFAULT NULL,
              `created_at` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `slug` (`slug`),
              KEY `name` (`name`),
              KEY `product_image_id_ibfk` (`image_id`),
              KEY `product_variant_id_ibfk` (`variant_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci
            SQL
        );

        $this->execute(
            <<<'SQL'
            CREATE TABLE `product_variant` (
              `id` bigint(20) unsigned NOT NULL,
              `product_id` bigint(20) unsigned NOT NULL,
              `image_id` bigint(20) unsigned DEFAULT NULL,
              `name` varchar(255) NOT NULL,
              `position` smallint(6) unsigned NOT NULL DEFAULT 0,
              `price` int(11) unsigned NOT NULL,
              `compare_at_price` int(11) unsigned DEFAULT NULL,
              `presentment_prices` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`presentment_prices`)),
              `option_1` varchar(255) DEFAULT NULL,
              `option_2` varchar(255) DEFAULT NULL,
              `option_3` varchar(255) DEFAULT NULL,
              `barcode` varchar(255) DEFAULT NULL,
              `sku` varchar(255) DEFAULT NULL,
              `is_taxable` tinyint(1) NOT NULL DEFAULT 0,
              `weight` varchar(255) DEFAULT NULL,
              `weight_unit` varchar(2) DEFAULT NULL,
              `inventory_quantity` int(11) DEFAULT NULL,
              `inventory_tracked` tinyint(1) unsigned NOT NULL DEFAULT 0,
              `inventory_policy` varchar(255) DEFAULT NULL,
              `unit_price` int(11) unsigned DEFAULT NULL,
              `unit_price_measurement` varchar(10) DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              `created_at` datetime NOT NULL,
              PRIMARY KEY (`id`),
              KEY `product_id` (`product_id`,`position`),
              KEY `product_variant_image_id_ibfk` (`image_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci
            SQL
        );

        $this->execute(
            <<<'SQL'
            CREATE TABLE `product_image` (
              `id` bigint(20) unsigned NOT NULL,
              `product_id` bigint(20) unsigned NOT NULL,
              `position` smallint(6) unsigned NOT NULL DEFAULT 0,
              `alt_text` varchar(255) DEFAULT NULL,
              `width` smallint(6) unsigned DEFAULT NULL,
              `height` smallint(6) unsigned DEFAULT NULL,
              `src` varchar(255) DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              `created_at` datetime NOT NULL,
              PRIMARY KEY (`id`,`product_id`),
              KEY `product_id` (`product_id`,`position`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci
            SQL
        );

        $this->execute(
            <<<'SQL'
            ALTER TABLE `product` ADD CONSTRAINT `product_variant_id_ibfk` FOREIGN KEY (`variant_id`) REFERENCES `product_variant` (`id`) ON DELETE SET NULL
            SQL
        );

        $this->execute(
            <<<'SQL'
            ALTER TABLE `product_variant` ADD CONSTRAINT `product_variant_product_id_ibfk` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE
            SQL
        );

        $this->execute(
            <<<'SQL'
            ALTER TABLE `product_image` ADD CONSTRAINT `product_image_product_id_ibfk` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE
            SQL
        );

        $this->execute(
            <<<'SQL'
            INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `updated_at`, `created_at`) VALUES
              ('shopifyProduct', '2', '{\"category\":\"shopify\",\"key\":\"AUTH_SHOPIFY_PRODUCT_DESCRIPTION\"}', NULL, NULL, '1789985581', '1789985581'),
              ('shopifyWebhook', '2', '{\"category\":\"shopify\",\"key\":\"AUTH_SHOPIFY_WEBHOOK_DESCRIPTION\"}', NULL, NULL, '1789985581', '1789985581')
            SQL
        );

        $this->execute(
            <<<'SQL'
            INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
              ('admin', 'shopifyProduct'),
              ('manager', 'shopifyProduct'),
              ('admin', 'shopifyWebhook'),
              ('manager', 'shopifyWebhook')
            SQL
        );
    }

    #[Override]
    public function safeDown(): bool
    {
        echo "    > a baseline cannot be reverted, restore a dump instead\n";
        return false;
    }
}
