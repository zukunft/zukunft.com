DROP PROCEDURE IF EXISTS value_text_prime_p4_delete_log;
CREATE PROCEDURE value_text_prime_p4_delete_log
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_text_value  smallint,
     _group_id             bigint,
     _phrase_id_1          smallint,
     _phrase_id_2          smallint,
     _phrase_id_3          smallint,
     _phrase_id_4          smallint)

BEGIN

    INSERT INTO change_values_text_prime ( user_id, change_action_id, change_field_id,        group_id)
         SELECT                           _user_id,_change_action_id,_field_id_text_value,_group_id ;

    DELETE
      FROM values_text_prime
     WHERE phrase_id_1 = _phrase_id_1
       AND phrase_id_2 = _phrase_id_2
       AND phrase_id_3 = _phrase_id_3
       AND phrase_id_4 = _phrase_id_4;

END;

SELECT value_text_prime_p4_delete_log
       (1,
        3,
        421,
        68962400101105737,
        245,
        240,
        226,
        -73);