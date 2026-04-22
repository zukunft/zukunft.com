PREPARE element_delete_by_ids FROM
    'DELETE FROM elements WHERE FIND_IN_SET(element_id, ?) > 0';
SET @ids = '208,209,210';
EXECUTE element_delete_by_ids USING @ids;