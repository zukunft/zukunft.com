PREPARE value_text_by_grp (bigint, text) AS
    SELECT     s.group_id,
               u.group_id AS user_group_id,
               s.user_id,
               CASE WHEN (u.text_value <> ''  IS NOT TRUE) THEN s.text_value  ELSE u.text_value  END AS text_value,
               CASE WHEN (u.source_id         IS     NULL) THEN s.source_id   ELSE u.source_id   END AS source_id,
               CASE WHEN (u.last_update       IS     NULL) THEN s.last_update ELSE u.last_update END AS last_update,
               CASE WHEN (u.excluded          IS     NULL) THEN s.excluded    ELSE u.excluded    END AS excluded,
               CASE WHEN (u.protect_id        IS     NULL) THEN s.protect_id  ELSE u.protect_id  END AS protect_id,
               u.user_id AS change_user_id,
               u.share_type_id
          FROM values_text s
     LEFT JOIN user_values_text u ON s.group_id = u.group_id
                                 AND u.user_id = $1
         WHERE s.group_id = $2;