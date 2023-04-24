PREPARE figure_list_by_2ids (int, int) AS
    SELECT
        figure_id,
        figure_name,
        phrase_group_id
    FROM figures
    WHERE figure_id IN ($1,$2);