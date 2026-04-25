PREPARE change_field_insert_01101 (text, text, smallint) AS
    INSERT INTO change_fields (change_field_name, code_id, table_id)
    VALUES       ($1, $2, $3)
    RETURNING change_field_id;