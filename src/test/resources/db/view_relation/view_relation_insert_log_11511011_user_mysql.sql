DROP PROCEDURE IF EXISTS view_relation_insert_log_11511011_user;
CREATE PROCEDURE view_relation_insert_log_11511011_user
    (_user_id                        bigint,
     _change_action_id               smallint,
     _field_id_view_relation_type_id smallint,
     _type_name                      text,
     _view_relation_type_id          smallint,
     _view_relation_id               bigint,
     _field_id_start_pos             smallint,
     _start_pos                      bigint,
     _field_id_description           smallint,
     _description                    text,
     _field_id_share_type_id         smallint,
     _share_type_id                  smallint,
     _field_id_protect_id            smallint,
     _protect_id                     smallint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,                new_value, new_id,                          row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_relation_type_id,_type_name,_view_relation_type_id,_view_relation_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,                 new_value,                                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_start_pos,             _start_pos,                       _view_relation_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,                 new_value,                                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,           _description,                     _view_relation_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,                 new_value,                                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_share_type_id,         _share_type_id,                   _view_relation_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,                 new_value,                                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,            _protect_id,                      _view_relation_id ;

    INSERT INTO user_view_relations
                ( view_relation_id, user_id, view_relation_type_id, start_pos, description, share_type_id, protect_id)
         SELECT  _view_relation_id,_user_id,_view_relation_type_id,_start_pos,_description,_share_type_id,_protect_id ;

END;

PREPARE view_relation_insert_log_11511011_user_call FROM
    'SELECT view_relation_insert_log_11511011_user (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT view_relation_insert_log_11511011_user (
               3,
               1,
               816,
               'add components',
               1,
               0,
               817,
               15,
               818,
               'add usage and log of a word',
               820,
               3,
               821,
               2);