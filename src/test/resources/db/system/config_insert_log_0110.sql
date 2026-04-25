CREATE OR REPLACE FUNCTION config_insert_log_0110
      (_code_id          text,
       _user_id          bigint,
       _change_action_id smallint,
       _field_id_code_id smallint,
       _field_id_value   smallint,
       _value            text) RETURNS bigint AS
$$
DECLARE new_config_id bigint;
BEGIN

    INSERT INTO config ( code_id)
         SELECT         _code_id
      RETURNING        config_id INTO new_config_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,_code_id,   new_config_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_value,  _value,     new_config_id ;

         UPDATE config
            SET value = _value
          WHERE config.config_id = new_config_id;

         RETURN new_config_id;

END
$$ LANGUAGE plpgsql;

PREPARE config_insert_log_0110_call
    (text, bigint, smallint, smallint, smallint, text) AS
SELECT config_insert_log_0110
    ($1, $2, $3, $4, $5, $6);

SELECT config_insert_log_0110
        ('version_database'::text,
         1::bigint,
         1::smallint,
         177::smallint,
         178::smallint,
         '0.0.2'::text);
