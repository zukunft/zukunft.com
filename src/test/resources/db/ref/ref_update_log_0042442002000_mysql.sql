DROP PROCEDURE IF EXISTS ref_update_log_0042442002000;
CREATE PROCEDURE ref_update_log_0042442002000
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_phrase_id   smallint,
     _from_phrase_name_old text,
     _phrase_id_old        bigint,
     _from_phrase_name     text,
     _phrase_id            bigint,
     _ref_id               bigint,
     _field_id_external_key smallint,
     _external_key_old     text,
     _external_key         text,
     _field_id_ref_type_id smallint,
     _type_name_old        text,
     _ref_type_id_old      smallint,
     _type_name            text,
     _ref_type_id          smallint,
     _field_id_description smallint,
     _description_old      text,
     _description          text)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,       old_value,            new_value,        old_id,          new_id,      row_id)
         SELECT         _user_id,_change_action_id,_field_id_phrase_id,   _from_phrase_name_old,_from_phrase_name,_phrase_id_old,  _phrase_id,  _ref_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,       old_value,            new_value,                                      row_id)
         SELECT         _user_id,_change_action_id,_field_id_external_key,_external_key_old,    _external_key,                                  _ref_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,       old_value,            new_value,        old_id,          new_id,      row_id)
         SELECT         _user_id,_change_action_id,_field_id_ref_type_id, _type_name_old,       _type_name,       _ref_type_id_old,_ref_type_id,_ref_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,      old_value,       new_value,                                            row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description_old,_description,                                         _ref_id ;

    UPDATE refs
       SET phrase_id    = _phrase_id,
           external_key = _external_key,
           ref_type_id  = _ref_type_id,
           description  = _description
      WHERE ref_id = _ref_id;

END;

PREPARE ref_update_log_0042442002000_call FROM
    'SELECT ref_update_log_0042442002000 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT ref_update_log_0042442002000 (
               3,
               2,
               159,
               'Pi',
               17,
               '',
               0,
               22,
               160,
               'Q167',
               '',
               161,
               'wikidata',
               2,
               null,
               null,
               65,
               'pi - ratio of the circumference of a circle to its diameter',
               null);