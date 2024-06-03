PREPARE formula_link_norm_by_id (bigint) AS
    SELECT formula_link_id,
           formula_id,
           phrase_id,
           user_id,
           formula_link_type_id,
           excluded,
           share_type_id,
           protect_id
    FROM formula_links
    WHERE formula_link_id = $1;
