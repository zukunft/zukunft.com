DROP PROCEDURE IF EXISTS formula_link_update_log_000000100;
CREATE PROCEDURE formula_link_update_log_000000100
    (_user_id           bigint,
     _change_action_id  smallint,
     _field_id_excluded smallint,
     _excluded_old      smallint,
     _excluded          smallint,
     _formula_link_id   bigint,
     _change_table_id   smallint,
     _old_text_from     text,
     _old_text_link     text,
     _old_text_to       text,
     _old_from_id       bigint,
     _old_link_id       smallint,
     _old_to_id         bigint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,   old_value,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,_excluded_old,_excluded, _formula_link_id ;
    INSERT INTO change_links ( user_id, change_action_id, change_table_id, old_text_from, old_text_link, old_text_to, old_from_id, old_link_id, old_to_id, row_id)
         SELECT               _user_id,_change_action_id,_change_table_id,_old_text_from,_old_text_link,_old_text_to,_old_from_id,_old_link_id,_old_to_id,_formula_link_id ;

    UPDATE formula_links
       SET excluded = _excluded
     WHERE formula_link_id = _formula_link_id;

END;

PREPARE formula_link_update_log_000000100_call FROM
    'SELECT formula_link_update_log_000000100 (?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT formula_link_update_log_000000100
       (3,
        2,
        675,
        null,
        1,
        1,
        92,
        'scale minute to sec',
        'time period based',
        'minute',
        1,
        2,
        100);