PREPARE change_table_insert_0110 (text, text) AS
    INSERT INTO change_tables (change_table_name, code_id)
    VALUES       ($1, $2)
    RETURNING change_table_id;