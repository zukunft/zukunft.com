PREPARE value_phrase_link_by_val_phr_usr_id (bigint, bigint, bigint) AS
    SELECT value_phrase_link_id, user_id, group_id, phrase_id, weight, link_type_id, condition_formula_id
    FROM value_phrase_links
    WHERE group_id = $1
      AND phrase_id = $2
      AND user_id = $3;

