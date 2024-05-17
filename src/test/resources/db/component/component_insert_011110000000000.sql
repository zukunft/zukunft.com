PREPARE component_insert_011110000000000 (bigint, text, text, smallint) AS
    INSERT INTO components (user_id, component_name, description, component_type_id)
         VALUES            ($1, $2, $3, $4)
      RETURNING component_id;