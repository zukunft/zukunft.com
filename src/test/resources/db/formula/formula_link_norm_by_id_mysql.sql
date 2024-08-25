PREPARE formula_link_norm_by_id FROM
   'SELECT formula_link_id,
           formula_id,
           phrase_id,
           user_id,
           formula_link_type_id,
           order_nbr,
           excluded,
           share_type_id,
           protect_id
    FROM formula_links
    WHERE formula_link_id = ?';