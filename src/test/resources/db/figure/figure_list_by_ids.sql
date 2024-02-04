PREPARE figure_list_by_ids (bigint[]) AS
    SELECT
            figure_id,
            figure_name,
            group_id
       FROM figures
      WHERE figure_id = ANY ($1);