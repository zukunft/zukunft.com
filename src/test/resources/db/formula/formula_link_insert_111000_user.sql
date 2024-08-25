PREPARE formula_link_insert_111000_user (bigint,bigint,smallint,bigint) AS
    INSERT INTO user_formula_links (formula_link_id,user_id,formula_link_type_id,order_nbr)
         VALUES ($1,$2,$3,$4)
      RETURNING formula_link_id;