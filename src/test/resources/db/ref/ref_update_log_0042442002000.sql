CREATE OR REPLACE FUNCTION ref_update_log_0042442002000 (
    _user_id              bigint,
    _change_action_id     smallint,
    _field_id_phrase_id   smallint,
    _from_phrase_name_old text,
    _phrase_id_old        bigint,
    _from_phrase_name     text,
    _phrase_id            bigint,
    _ref_id               bigint,
    _field_id_ref_type_id smallint,
    _type_name_old        text,
    _ref_type_id_old      smallint,
    _type_name            text,
    _ref_type_id          smallint,
    _field_id_external_key smallint,
    _external_key_old     text,
    _external_key         text,
    _field_id_description smallint,
    _description_old      text,
    _description          text) RETURNS void AS $$

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,       old_value,            new_value,        old_id,          new_id,      row_id)
         SELECT         _user_id,_change_action_id,_field_id_phrase_id,   _from_phrase_name_old,_from_phrase_name,_phrase_id_old,  _phrase_id,  _ref_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,       old_value,            new_value,        old_id,          new_id,      row_id)
         SELECT         _user_id,_change_action_id,_field_id_ref_type_id, _type_name_old,       _type_name,       _ref_type_id_old,_ref_type_id,_ref_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,       old_value,            new_value,                                      row_id)
         SELECT         _user_id,_change_action_id,_field_id_external_key,_external_key_old,    _external_key,                                  _ref_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,       old_value,            new_value,                                      row_id)
         SELECT         _user_id,_change_action_id,_field_id_description, _description_old,     _description,                                   _ref_id ;

    UPDATE refs
       SET phrase_id    = _phrase_id,
           ref_type_id  = _ref_type_id,
           external_key = _external_key,
           description  = _description
     WHERE ref_id = _ref_id;

END $$ LANGUAGE plpgsql;

PREPARE ref_update_log_0042442002000_call
        (bigint, smallint, smallint, text, bigint, text, bigint, bigint, smallint, text, smallint, text, smallint, smallint, text, text, smallint, text, text) AS
SELECT ref_update_log_0042442002000
        ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19);

SELECT ref_update_log_0042442002000 (
               3::bigint,
               2::smallint,
               159::smallint,
               'Pi'::text,
               17::bigint,
               null::text,
               0::bigint,
               29::bigint,
               161::smallint,
               'wikidata'::text,
               2::smallint,
               null::text,
               null::smallint,
               160::smallint,
               'Q167'::text,
               null::text,
               65::smallint,
               'pi - ratio of the circumference of a circle to its diameter'::text,
               null::text);