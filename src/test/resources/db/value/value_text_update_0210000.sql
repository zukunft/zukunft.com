PREPARE value_text_update_0210000 (text, text) AS
    UPDATE values_text
       SET text_value  = $1,
           last_update = Now()
     WHERE group_id = $2;