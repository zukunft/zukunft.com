DROP PROCEDURE IF EXISTS view_relation_insert_log_0155510000;
CREATE PROCEDURE view_relation_insert_log_0155510000
    (_parent_view_id         bigint,
     _view_relation_type_id  smallint,
     _child_view_id          bigint,
     _user_id                bigint,
     _change_action_id       smallint,
     _change_table_id        smallint,
     _new_text_from          text,
     _new_text_link          text,
     _new_text_to            text,
     _field_id_user_id       smallint,
     _field_id_start_pos     smallint,
     _start_pos              bigint)

BEGIN

    INSERT INTO view_relations (parent_view_id, view_relation_type_id, child_view_id)
         SELECT                 _parent_view_id,_view_relation_type_id,_child_view_id ;

    SELECT LAST_INSERT_ID() AS @new_view_relation_id;

    INSERT INTO change_links (user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id,       new_link_id, new_to_id,                 row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,    _parent_view_id,_view_relation_type_id,  _child_view_id, @new_view_relation_id ;

    INSERT INTO changes (user_id, change_action_id,  change_field_id, new_value,               row_id)
         SELECT         _user_id,_change_action_id,_field_id_user_id,  _user_id,@new_view_relation_id ;

    INSERT INTO changes (user_id, change_action_id,  change_field_id, new_value,               row_id)
         SELECT         _user_id,_change_action_id,_field_id_start_pos,  _start_pos,@new_view_relation_id ;

    UPDATE view_relations
       SET user_id = _user_id,
           start_pos = _start_pos
     WHERE view_relations.view_relation_id = @new_view_relation_id;

END;

PREPARE view_relation_insert_log_0155510000_call FROM
    'SELECT view_relation_insert_log_0155510000 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT view_relation_insert_log_0155510000 (
               3,
               1,
               5,
               3,
               1,
               108,
               'word_edit',
               'standard',
               'word_usage',
               815,
               817,
               15);