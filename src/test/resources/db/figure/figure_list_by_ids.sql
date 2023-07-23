PREPARE figure_list_by_ids (int[]) AS
    SELECT
        figure_id,
        figure_name,
        phrase_group_id
    FROM figures
    WHERE figure_id = ANY ($1);