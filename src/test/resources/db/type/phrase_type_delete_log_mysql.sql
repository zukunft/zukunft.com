DROP PROCEDURE IF EXISTS phrase_type_delete_log;
CREATE PROCEDURE phrase_type_delete_log
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_type_name smallint,
     _type_name          text,
     _phrase_type_id     bigint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,_type_name,_phrase_type_id ;

         DELETE
           FROM phrase_types
          WHERE phrase_type_id = _phrase_type_id;

END;

SELECT phrase_type_delete_log
       (1,
        3,
        835,
        'standard',
        1);