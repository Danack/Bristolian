<?php

declare(strict_types = 1);


function getAllQueries_29(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `tinned_fish_product` (
  `id` varchar(36) NOT NULL,
  `barcode` varchar(20) NOT NULL COMMENT 'EAN/UPC/GTIN barcode',
  `name` varchar(512) NOT NULL COMMENT 'Product name',
  `brand` varchar(256) NOT NULL COMMENT 'Brand name',
  `species` varchar(128) DEFAULT NULL COMMENT 'Fish species e.g. Sardines, Tuna',
  `weight` decimal(10,2) DEFAULT NULL COMMENT 'Total product weight in grams',
  `weight_drained` decimal(10,2) DEFAULT NULL COMMENT 'Drained weight in grams',
  `product_code` varchar(64) DEFAULT NULL COMMENT 'Internal normalization code for grouping same products',
  `image_url` varchar(1024) DEFAULT NULL COMMENT 'URL to product image',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_tinned_fish_product_id UNIQUE (id),
  CONSTRAINT uc_tinned_fish_product_barcode UNIQUE (barcode),
  INDEX idx_tinned_fish_product_barcode (barcode),
  INDEX idx_tinned_fish_product_product_code (product_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Canonical product database for Tinned Fish Diary.";

SQL;

    return $sql;
}

function getDescription_29(): string
{
    return 'Tinned Fish Diary product table';
}
