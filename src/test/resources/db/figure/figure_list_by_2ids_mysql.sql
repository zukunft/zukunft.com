PREPARE figure_list_by_2ids FROM
    'SELECT
        figure_id,
        figure_name,
        phrase_group_id
    FROM figures
    WHERE figure_id IN (?,?)';