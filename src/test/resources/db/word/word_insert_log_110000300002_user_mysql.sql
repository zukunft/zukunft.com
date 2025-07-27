DROP PROCEDURE IF EXISTS word_insert_log_110000300002_user;
CREATE PROCEDURE word_insert_log_110000300002_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_view_id        smallint,
     _view_name_old           text,
     _view_id_old             bigint,
     _view_name               text,
     _view_id                 bigint,
     _word_id                 bigint,
     _field_id_protect_id     smallint,
     _protect_id_old          smallint,
     _protect_id              smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,old_value,old_id, row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_id,_view_name_old,_view_id_old, _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value,row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,_protect_id_old,_word_id ;

    INSERT INTO user_words
                (word_id, user_id, view_id, protect_id)
         SELECT _word_id,_user_id,_view_id, _protect_id ;

END;

PREPARE word_insert_log_110000300002_user_call FROM
    'SELECT word_insert_log_110000300002_user (?,?,?,?,?,?,?,?,?,?,?)';

SELECT word_insert_log_110000300002_user
        (1,
         1,
         85,
         '',
         51,
         null,
         null,
         272,
         87,
         3,
         null);