PREPARE formula_link_by_usr_cfg (bigint, bigint) AS
    SELECT formula_link_id,
           formula_link_type_id,
           order_nbr,
           excluded,
           share_type_id,
           protect_id
      FROM user_formula_links
     WHERE formula_link_id = $1
       AND user_id = $2;