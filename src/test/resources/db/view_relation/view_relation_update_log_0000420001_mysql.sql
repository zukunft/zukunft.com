DROP PROCEDURE IF EXISTS view_relation_update_log_0000420001;
CREATE PROCEDURE view_relation_update_log_0000420001
    (_user_id                        bigint,
     _change_action_id               smallint,
     _field_id_view_relation_type_id smallint,
     _type_name_old                  text,
     _view_relation_type_id_old      smallint,
     _type_name                      text,
     _view_relation_type_id          smallint,
     _view_relation_id               bigint,
     _field_id_start_pos             smallint,
     _start_pos_old                  bigint,
     _start_pos                      bigint,
     _field_id_protect_id            smallint,
     _protect_id_old                 smallint,
     _protect_id                     smallint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,                old_value,        new_value,    old_id,                    new_id,                row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_relation_type_id,_type_name_old,   _type_name,   _view_relation_type_id_old,_view_relation_type_id,_view_relation_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,                old_value,        new_value,                                                      row_id)
         SELECT          _user_id,_change_action_id,_field_id_start_pos,           _start_pos_old,    _start_pos,                                                     _view_relation_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,                old_value,        new_value,                                                      row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,           _protect_id_old,  _protect_id,                                                    _view_relation_id ;

    UPDATE view_relations
       SET view_relation_type_id = _view_relation_type_id,
           start_pos             = _start_pos,
           protect_id            = _protect_id
     WHERE view_relation_id = _view_relation_id;

END;

PREPARE view_relation_update_log_0000420001_call FROM
    'SELECT view_relation_update_log_0000420001 (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT view_relation_update_log_0000420001 (
               3,
               2,
               816,
               'standard',
               1,
               null,
               null,
               1,
               817,
               15,
               null,
               821,
               null,
               2);