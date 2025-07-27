DROP PROCEDURE IF EXISTS triple_update_log_000022400000002;
CREATE PROCEDURE triple_update_log_000022400000002
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
         _phrase_type_name_old    text,
         _phrase_type_id_old      smallint,
         _phrase_type_name        text,
         _phrase_type_id          smallint,
         _field_id_protect_id     smallint,
         _protect_id_old          smallint,
         _protect_id              smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name_old,_triple_name,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,        old_id,             new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name_old,_phrase_type_name,_phrase_type_id_old,_phrase_type_id,_triple_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,     old_value,      new_value,  row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,_protect_id_old,_protect_id,_triple_id ;

    UPDATE triples
       SET triple_name    = _triple_name,
           description    = _description,
           phrase_type_id = _phrase_type_id,
           protect_id     = _protect_id
     WHERE triple_id = _triple_id;

END;

PREPARE triple_update_log_000022400000002_call FROM
    'SELECT triple_update_log_000022400000002 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT triple_update_log_000022400000002
       (1,
        2,
        18,
        'mathematical constant',
        'System Test Word Renamed',
        1,
        68,
        'A mathematical constant that never changes e.g. Pi',
        null,
        69,
        'constant',
        17,
        null,
        null,
        97,
        3,
        null);