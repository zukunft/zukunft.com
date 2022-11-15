PREPARE ref_by_id (int) AS
    SELECT
        ref_id,
        phrase_id,
        ref_type_id,
        external_key
    FROM refs
   WHERE ref_id = $1;