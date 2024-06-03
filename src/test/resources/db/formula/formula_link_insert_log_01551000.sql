CREATE OR REPLACE FUNCTION formula_link_insert_log_01551000
    (_formula_id           bigint,
     _formula_link_type_id smallint,
     _phrase_id            bigint,
     _user_id              bigint,
     _change_action_id     smallint,
     _change_table_id      smallint,
     _new_text_from        text,
     _new_text_link        text,
     _new_text_to          text,
     _field_id_user_id     smallint,
     _field_id_order_nbr   smallint,
     _order_nbr            bigint) RETURNS bigint AS
$$
DECLARE new_formula_link_id bigint;
BEGIN

    INSERT INTO formula_links (formula_id, formula_link_type_id, phrase_id)
         SELECT               _formula_id,_formula_link_type_id,_phrase_id
      RETURNING formula_link_id INTO new_formula_link_id;

    INSERT INTO change_links (user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id, new_link_id,           new_to_id,   row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,_formula_id, _formula_link_type_id, _phrase_id,new_formula_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,  new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_user_id,_user_id,  new_formula_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,    new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr,new_formula_link_id ;

    UPDATE formula_links
       SET user_id = _user_id,
           order_nbr = _order_nbr
     WHERE formula_links.formula_link_id = new_formula_link_id;

    RETURN new_formula_link_id;

END
$$ LANGUAGE plpgsql;

PREPARE formula_link_insert_log_01551000_call
    (bigint,smallint,bigint,bigint,smallint,smallint,text,text,text,smallint,smallint,bigint) AS
SELECT formula_link_insert_log_01551000
    ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12);

SELECT formula_link_insert_log_01551000
    (1::bigint,
     2::smallint,
     1::bigint,
     1::bigint,
     1::smallint,
     12::smallint,
     'scale minute to sec'::text,
     'time period based'::text,
     'Mathematics'::text,
     699::smallint,
     700::smallint,
     2::bigint);