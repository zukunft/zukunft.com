PREPARE component_insert_111110000000000_user (bigint, bigint, text, text, smallint) AS
    INSERT INTO user_components (component_id, user_id, component_name, description, component_type_id)
         VALUES                 ($1, $2, $3, $4, $5)
      RETURNING component_id;
