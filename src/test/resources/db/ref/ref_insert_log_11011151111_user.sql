CREATE OR REPLACE FUNCTION ref_insert_log_11011151111_user
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
     _new_from_id            bigint,
     _new_link_id            smallint,
     _new_to_id              bigint,
     _field_id_url           smallint,
     _url                    text,
     _ref_id                 bigint,
     _field_id_source_id     smallint,
     _source_name            text,
     _source_id              bigint,
     _field_id_description   smallint,
     _description            text,
     _field_id_excluded      smallint,
     _excluded               smallint,
     _field_id_share_type_id smallint,
     _share_type_id          smallint,
     _field_id_protect_id    smallint,
     _protect_id             smallint) RETURNS bigint AS

$$
BEGIN

    INSERT INTO change_links (user_id, change_action_id, change_table_id, old_text_from, old_text_link, old_text_to, new_text_from, new_text_link, new_text_to, new_from_id, new_link_id, new_to_id, row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_old_text_from,_old_text_link,_old_text_to,_new_text_from,_new_text_link,_new_text_to,_new_from_id,_new_link_id,_new_to_id,_ref_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id, new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_url,   _url,      _ref_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,    new_value,   new_id,    row_id)
         SELECT         _user_id,_change_action_id,_field_id_source_id,_source_name,_source_id,_ref_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description,_ref_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,   new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_excluded,_excluded, _ref_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,        new_value,      row_id)
         SELECT         _user_id,_change_action_id,_field_id_share_type_id,_share_type_id, _ref_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,     new_value,   row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,_protect_id, _ref_id ;

    INSERT INTO user_refs (ref_id, user_id, url, source_id, description, excluded, share_type_id, protect_id)
         SELECT           _ref_id,_user_id,_url,_source_id,_description,_excluded,_share_type_id,_protect_id ;

END
$$ LANGUAGE plpgsql;

PREPARE ref_insert_log_11011151111_user_call
        (bigint,smallint,smallint,text,text,text,text,text,text,bigint,smallint,bigint,smallint,bigint,smallint,text,bigint,smallint,text,bigint,smallint,text,smallint,smallint,smallint,smallint,smallint,smallint) AS
SELECT ref_insert_log_11011151111_user
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,$27,$28);

SELECT ref_insert_log_11011151111_user (
               1::bigint,
               1::smallint,
               22::smallint,
               ''::text,
               null::text,
               null::text,
               null::text,
               null::text,
               null::text,
               0::bigint,
               null::smallint,
               null::bigint,
               null::smallint,
               null::bigint,
               66::smallint,
               'https://www.wikidata.org/wiki/Special:EntityData/Q167.json'::text,
               4::bigint,
               67::smallint,
               'The International System of Units'::text,
               1::bigint,
               65::smallint,
               'pi - ratio of the circumference of a circle to its diameter'::text,
               162::smallint,
               1::smallint,
               247::smallint,
               3::smallint,
               248::smallint,
               2::smallint);

