CREATE OR REPLACE FUNCTION view_relation_insert_log_11511011_user
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
       _protect_id                     smallint) RETURNS bigint AS

$$
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

END $$ LANGUAGE plpgsql;

PREPARE view_relation_insert_log_11511011_user_call
        (bigint, smallint, smallint, text, smallint, bigint, smallint, bigint, smallint, text, smallint, smallint, smallint, smallint)
AS SELECT view_relation_insert_log_11511011_user
        ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14);

SELECT view_relation_insert_log_11511011_user
    (3::bigint,
     1::smallint,
     816::smallint,
     'add components'::text,
     1::smallint,
     0::bigint,
     817::smallint,
     15::bigint,
     818::smallint,
     'add usage and log of a word'::text,
     820::smallint,
     3::smallint,
     821::smallint,
     2::smallint);