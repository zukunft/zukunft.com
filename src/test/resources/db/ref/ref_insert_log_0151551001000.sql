CREATE OR REPLACE FUNCTION ref_insert_log_0151551001000
    (_phrase_id            bigint,
     _ref_type_id          smallint,
     _external_key         text,
     _user_id              bigint,
     _change_action_id     smallint,
     _change_table_id      smallint,
     _new_text_from        text,
     _new_text_link        text,
     _new_text_to          text,
     _field_id_user_id     smallint,
     _field_id_description smallint,
     _description          text) RETURNS bigint AS
$$
DECLARE new_ref_id bigint;
BEGIN

    INSERT INTO refs (phrase_id, ref_type_id, external_key)
         SELECT      _phrase_id,_ref_type_id,_external_key
      RETURNING       ref_id INTO new_ref_id;

    INSERT INTO change_links (user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id, new_link_id, row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,_phrase_id,  _ref_type_id, new_ref_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id, new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_user_id,_user_id,  new_ref_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description, new_ref_id ;

    UPDATE refs
       SET user_id     = _user_id,
           description = _description
     WHERE refs.ref_id = new_ref_id;

    RETURN new_ref_id;

END
$$ LANGUAGE plpgsql;

PREPARE ref_insert_log_0151551001000_call
        (bigint,smallint,text,bigint,smallint,smallint,text,text,text,smallint,smallint,text) AS
SELECT ref_insert_log_0151551001000
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12);

SELECT ref_insert_log_0151551001000 (
               4::bigint,
               2::smallint,
               'Q167'::text,
               1::bigint,
               1::smallint,
               22::smallint,
               'Pi'::text,
               'wikidata'::text,
               'Q167'::text,
               246::smallint,
               65::smallint,
               'pi - ratio of the circumference of a circle to its diameter'::text);