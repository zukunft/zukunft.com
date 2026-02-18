CREATE OR REPLACE FUNCTION config_update_log_1021
      (_user_id              bigint,
       _change_action_id     smallint,
       _field_id_config_name smallint,
       _config_name_old      text,
       _config_name          text,
       _config_id            smallint,
       _field_id_value       smallint,
       _value_old            text,
       _value                text,
       _field_id_description smallint,
       _description_old      text,
       _description          text) RETURNS void AS
$$

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_config_name,_config_name_old,_config_name,_config_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_value,      _value_old,      _value,      _config_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_config_id ;

         UPDATE config
            SET config_name = _config_name,
                value       = _value,
                description = _description
          WHERE config_id = _config_id;

END
$$ LANGUAGE plpgsql;

PREPARE config_update_log_1021_call
    (bigint, smallint, smallint, text, text, smallint, smallint, text, text, smallint, text, text) AS
SELECT config_update_log_1021
    ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12);

SELECT config_update_log_1021
        (1::bigint,
         1::smallint,
         176::smallint,
         null::text,
         'Database version'::text,
         0::smallint,
         178::smallint,
         '0.0.2'::text,
         '0.0.3'::text,
         179::smallint,
         null::text,
         'version that the database has now; after the upgrade the new version number is written to the database'::text);



