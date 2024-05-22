CREATE OR REPLACE FUNCTION triple_insert_log_1100010000100_user
    (_user_id           bigint,
     _change_action_id  smallint,
     _change_table_id   smallint,
     _old_text_from     text,
     _old_text_link     text,
     _old_text_to       text,
     _new_text_from     text,
     _new_text_link     text,
     _new_text_to       text,
     _old_from_id       bigint,
     _old_link_id       smallint,
     _old_to_id         bigint,
     _new_from_id       bigint,
     _new_link_id       smallint,
     _new_to_id         bigint,
     _field_id_excluded smallint,
     _excluded          smallint,
     _triple_id         bigint) RETURNS bigint AS
$$
BEGIN

    INSERT INTO change_links (user_id, change_action_id, change_table_id, old_text_from, old_text_link, old_text_to, new_text_from, new_text_link, new_text_to, old_from_id, old_link_id, old_to_id, new_from_id, new_link_id, new_to_id, row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_old_text_from,_old_text_link,_old_text_to,_new_text_from,_new_text_link,_new_text_to,_old_from_id,_old_link_id,_old_to_id,_new_from_id,_new_link_id,_new_to_id,_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,_excluded,_triple_id ;


    INSERT INTO user_triples ( triple_id, user_id, excluded)
         SELECT               _triple_id,_user_id,_excluded ;

END
$$ LANGUAGE plpgsql;

PREPARE triple_insert_log_1100010000100_user_call
        (bigint, smallint, smallint, text, text, text, text, text, text, bigint, smallint, bigint, bigint, smallint, bigint, smallint, smallint, bigint) AS
    SELECT triple_insert_log_1100010000100_user
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18);

SELECT triple_insert_log_1100010000100_user (
               1::bigint,
               1::smallint,
               7::smallint,
               'constant'::text,
               'contains'::text,
               'Mathematics'::text,
               null::text,
               null::text,
               null::text,
               2::bigint,
               3::smallint,
               1::bigint,
               null::bigint,
               null::smallint,
               null::bigint,
               21::smallint,
               1::smallint,
               1::bigint);