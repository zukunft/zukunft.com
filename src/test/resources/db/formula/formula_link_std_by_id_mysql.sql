PREPARE formula_link_std_by_id FROM
   'SELECT formula_link_id,
           formula_id,
           phrase_id,
           user_id,
           link_type_id,
           excluded,
           share_type_id,
           protect_id
    FROM formula_links
    WHERE formula_link_id = ?';