DROP PROCEDURE IF EXISTS component_link_insert_log_110100100_user;
CREATE PROCEDURE component_link_insert_log_110100100_user
    (_user_id                bigint,
     _change_action_id       smallint,
     _change_table_id        smallint,
     _old_text_from          text,
     _old_text_link          text,
     _old_text_to            text,
     _new_text_from          text,
     _new_text_link          text,
     _new_text_to            text,
     _old_from_id            bigint,
     _old_link_id            smallint,
     _old_to_id              bigint,
     _new_from_id            bigint,
     _new_link_id            smallint,
     _new_to_id              bigint,
     _field_id_order_nbr     smallint,
     _order_nbr              bigint,
     _component_link_id      bigint,
     _field_id_excluded      smallint,
     _excluded               smallint)
BEGIN

    INSERT INTO change_links (user_id, change_action_id, change_table_id, old_text_from, old_text_link, old_text_to, new_text_from, new_text_link, new_text_to, old_from_id, old_link_id, old_to_id, new_from_id, new_link_id, new_to_id, row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_old_text_from,_old_text_link,_old_text_to,_new_text_from,_new_text_link,_new_text_to,_old_from_id,_old_link_id,_old_to_id,_new_from_id,_new_link_id,_new_to_id,_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr,_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,   new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_excluded,_excluded, _component_link_id ;

    INSERT INTO user_component_links (component_link_id, user_id, order_nbr, excluded)
         SELECT                      _component_link_id,_user_id,_order_nbr,_excluded ;

END;

PREPARE component_link_insert_log_110100100_user_call FROM
    'SELECT component_link_insert_log_110100100_user (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT component_link_insert_log_110100100_user
       (1,
        1,
        16,
        'Start view',
        null,
        'Word',
        null,
        null,
        null,
        1,
        null,
        1,
        null,
        null,
        null,
        48,
        1,
        1,
        49,
        1);
