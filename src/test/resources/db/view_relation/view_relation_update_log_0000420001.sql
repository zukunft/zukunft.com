CREATE OR REPLACE FUNCTION view_relation_update_log_0000420001
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
     _protect_id                     smallint) RETURNS void AS

$$ BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,                old_value,        new_value,    old_id,                    new_id,                row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_relation_type_id,_type_name_old,   _type_name,   _view_relation_type_id_old,_view_relation_type_id,_view_relation_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,                old_value,        new_value,                                                      row_id)
         SELECT          _user_id,_change_action_id,_field_id_start_pos,            _start_pos_old,   _start_pos,                                                     _view_relation_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,                old_value,        new_value,                                                      row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,           _protect_id_old,  _protect_id,                                                    _view_relation_id ;

    UPDATE view_relations
       SET view_relation_type_id = _view_relation_type_id,
           start_pos             = _start_pos,
           protect_id            = _protect_id
     WHERE view_relation_id = _view_relation_id;

END $$ LANGUAGE plpgsql;

PREPARE view_relation_update_log_0000420001_call
    (bigint, smallint, smallint, text, smallint, text, smallint, bigint, smallint, bigint, bigint, smallint, smallint, smallint) AS
SELECT view_relation_update_log_0000420001
    ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14);

SELECT view_relation_update_log_0000420001 (
               3::bigint,
               2::smallint,
               816::smallint,
               'add components'::text,
               1::smallint,
               null::text,
               null::smallint,
               1::bigint,
               817::smallint,
               15::bigint,
               null::bigint,
               821::smallint,
               null::smallint,
               2::smallint);