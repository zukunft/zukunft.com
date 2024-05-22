CREATE OR REPLACE FUNCTION formula_insert_log_01111111111111
    (_formula_name               text,
     _user_id                    bigint,
     _change_action_id           smallint,
     _field_id_formula_name      smallint,
     _field_id_user_id           smallint,
     _field_id_description       smallint,
     _description                text,
     _field_id_formula_type_id   smallint,
     _formula_type_id            bigint,
     _field_id_formula_text      smallint,
     _formula_text               text,
     _field_id_resolved_text     smallint,
     _resolved_text              text,
     _field_id_all_values_needed smallint,
     _all_values_needed          smallint,
     _field_id_last_update       smallint,
     _last_update                timestamp,
     _field_id_view_id           smallint,
     _view_name                  text,
     _view_id                    bigint,
     _field_id_usage             smallint,
     _usage                      bigint,
     _field_id_excluded          smallint,
     _excluded                   smallint,
     _field_id_share_type_id     smallint,
     _share_type_id              smallint,
     _field_id_protect_id        smallint,
     _protect_id                 smallint) RETURNS bigint AS
$$
DECLARE new_formula_id bigint;
BEGIN

    INSERT INTO formulas ( formula_name)
         SELECT           _formula_name
      RETURNING            formula_id INTO new_formula_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,           new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,    _formula_name,               new_formula_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,           new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,         _user_id,                    new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,      _description,                new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_type_id,  _formula_type_id,            new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_text,     _formula_text,               new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_resolved_text,    _resolved_text,              new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_all_values_needed,_all_values_needed,          new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_last_update,      _last_update,                new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,         new_id,  row_id)
         SELECT         _user_id,_change_action_id,_field_id_view_id,          _view_name,        _view_id, new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_usage,            _usage,                      new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_excluded,         _excluded,                   new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_share_type_id,    _share_type_id,              new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,       _protect_id,                 new_formula_id ;

    UPDATE formulas
       SET user_id           = _user_id,
           description       = _description,
           formula_type_id   = _formula_type_id,
           formula_text      = _formula_text,
           resolved_text     = _resolved_text,
           all_values_needed = _all_values_needed,
           last_update       = _last_update,
           view_id           = _view_id,
           usage             = _usage,
           excluded          = _excluded,
           share_type_id     = _share_type_id,
           protect_id        = _protect_id
     WHERE formulas.formula_id = new_formula_id;

    RETURN new_formula_id;

END
$$ LANGUAGE plpgsql;

PREPARE formula_insert_log_01111111111111_call
        (text, bigint, smallint, smallint, smallint, smallint, text, smallint, bigint, smallint, text, smallint, text, smallint, smallint, smallint, timestamp, smallint, text, bigint, smallint, bigint, smallint, smallint, smallint, smallint, smallint, smallint) AS
    SELECT formula_insert_log_01111111111111
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,$27,$28);

SELECT formula_insert_log_01111111111111 (
               'scale minute to sec'::text,
               1::bigint,
               1::smallint,
               30::smallint,
               173::smallint,
               34::smallint,
               'to convert times in minutes to seconds and the other way round'::text,
               31::smallint,
               1::bigint,
               33::smallint,
               '{w17}={w98}*60'::text,
               32::smallint,
               '"second" = "minute" * 60'::text,
               35::smallint,
               1::smallint,
               116::smallint,
               '2023-01-03T20:59:59+01:00'::timestamp,
               655::smallint,
               ''::text,
               1::bigint,
               656::smallint,
               2::bigint,
               71::smallint,
               1::smallint,
               117::smallint,
               3::smallint,
               118::smallint,
               2::smallint);
