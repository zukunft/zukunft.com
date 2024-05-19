DROP PROCEDURE IF EXISTS triple_update_log_0011100000000_user;
CREATE PROCEDURE triple_update_log_0011100000000_user
(_user_id                 bigint,
 _change_action_id        smallint,
 _field_id_triple_name    smallint,
 _triple_name_old         text,
 _triple_name             text,
 _triple_id               bigint,
 _field_id_description    smallint,
 _description_old         text,
 _description             text,
 _field_id_phrase_type_id smallint,
 _phrase_type_id_old      smallint,
 _phrase_type_id          smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,         new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name_old,_triple_name,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,          new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_id_old,_phrase_type_id,_triple_id ;

    UPDATE user_triples
       SET triple_name    = _triple_name,
           description    = _description,
           phrase_type_id = _phrase_type_id
     WHERE triple_id = _triple_id
       AND user_id = _user_id;

END;

PREPARE triple_update_log_0011100000000_user_call FROM
    'SELECT triple_update_log_0011100000000_user (?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT triple_update_log_0011100000000_user
       (1,
        2,
        18,
        'Mathematical constant',
        'System Test Word Renamed',
        1,
        68,
        'A mathematical constant that never changes e.g. Pi',
        null,
        69,
        17,
        null);