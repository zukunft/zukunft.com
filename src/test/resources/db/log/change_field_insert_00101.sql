PREPARE change_field_insert_00101 (text, smallint) AS
    INSERT INTO change_fields (code_id, table_id)
         VALUES ($1, $2)
      RETURNING change_field_id;
