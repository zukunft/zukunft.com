PREPARE element_insert_011111 (bigint, bigint, bigint, bigint) AS
    INSERT INTO elements (user_id, formula_id, element_type_id, ref_id)
         VALUES          ($1, $2, $3, $4)
      RETURNING element_id;