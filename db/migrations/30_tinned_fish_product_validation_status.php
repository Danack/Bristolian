<?php

declare(strict_types = 1);


function getAllQueries_30(): array
{
    $sql = [];

    $sql[] = <<< SQL
ALTER TABLE `tinned_fish_product`
ADD COLUMN `validation_status` varchar(32) NOT NULL DEFAULT 'not_validated' 
COMMENT 'Validation status: not_validated, validated_not_fish, validated_is_fish'
AFTER `image_url`;
SQL;

    $sql[] = <<< SQL
CREATE INDEX idx_tinned_fish_product_validation_status ON `tinned_fish_product` (`validation_status`);
SQL;

    return $sql;
}

function getDescription_30(): string
{
    return 'Add validation_status column to tinned_fish_product table';
}
