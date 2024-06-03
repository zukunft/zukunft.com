DROP PROCEDURE IF EXISTS formula_link_insert_log_01551000;
CREATE PROCEDURE formula_link_insert_log_01551000
    (_formula_id           bigint,
     _formula_link_type_id smallint,
     _phrase_id           bigint,
     _user_id              bigint,
     _change_action_id     smallint,
     _change_table_id      smallint,
     _new_text_from        text,
     _new_text_link        text,
     _new_text_to          text,
     _field_id_user_id     smallint,
     _field_id_order_nbr   smallint,
     _order_nbr            bigint)
BEGIN

    INSERT INTO formula_links (formula_id, formula_link_type_id, phrase_id)
         SELECT               _formula_id,_formula_link_type_id,_phrase_id ;

    SELECT LAST_INSERT_ID() AS @new_formula_link_id;

    INSERT INTO change_links (user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id, new_link_id,          new_to_id, row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,_formula_id, _formula_link_type_id,_phrase_id,@new_formula_link_id ;

    INSERT INTO changes (user_id,change_action_id,change_field_id,new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_user_id,_user_id,@new_formula_link_id ;

    INSERT INTO changes (user_id,change_action_id,change_field_id,new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr,@new_formula_link_id ;

    UPDATE formula_links
       SET user_id = _user_id,
           order_nbr = _order_nbr
     WHERE formula_links.formula_link_id = @new_formula_link_id;

END;

PREPARE formula_link_insert_log_01551000_call FROM
    'SELECT formula_link_insert_log_01551000 (?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT formula_link_insert_log_01551000
       (1,
        2,
        1,
        1,
        1,
        12,
        'scale minute to sec',
        'time period based',
        'Mathematics',
        699,
        700,
        2);