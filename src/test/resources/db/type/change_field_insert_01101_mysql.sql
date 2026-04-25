PREPARE change_field_insert_01101 FROM
    'INSERT INTO change_fields (change_field_name, code_id, table_id)
    VALUES       (?, ?, ?)';