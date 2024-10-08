PREPARE change_values_norm_update
    (bigint,smallint,smallint,numeric,numeric,text) AS
        INSERT INTO change_values_norm
            (user_id,change_action_id,change_field_id,old_value,new_value,group_id)
        VALUES
            ($1,$2,$3,$4,$5,$6)
        RETURNING
            change_id;