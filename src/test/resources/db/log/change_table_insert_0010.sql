PREPARE change_table_insert_0010 (text) AS
    INSERT INTO change_tables (code_id)
         VALUES ($1)
      RETURNING change_table_id;