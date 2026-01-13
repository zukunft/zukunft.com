PREPARE element_insert_11111 (bigint, bigint, bigint, bigint, bigint) AS
    INSERT INTO elements (element_id, formula_id, element_type_id, user_id, ref_id)
         VALUES          ($1, $2, $3, $4, $5)
      RETURNING element_id;