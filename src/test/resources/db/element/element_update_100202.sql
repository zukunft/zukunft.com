PREPARE element_update_100202 (bigint, bigint, bigint, bigint) AS
    UPDATE elements
       SET element_id      = $1,
           element_type_id = $2,
           ref_id          = $3
     WHERE element_id = $4;
