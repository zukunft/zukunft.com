PREPARE value_phrase_link_by_val_phr_usr_id FROM
    'SELECT value_phrase_link_id, user_id, value_id, phrase_id, weight, link_type_id, condition_formula_id
    FROM value_phrase_links
    WHERE value_id = ?
        AND phrase_id = ?
        AND user_id = ?';