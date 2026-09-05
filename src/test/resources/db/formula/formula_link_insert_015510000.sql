PREPARE formula_link_insert_015510000 (bigint, bigint, bigint, smallint, bigint) AS
    INSERT INTO formula_links (user_id, formula_id, phrase_id, formula_link_type_id, order_nbr)
         VALUES               ($1, $2, $3, $4, $5)
      RETURNING formula_link_id;