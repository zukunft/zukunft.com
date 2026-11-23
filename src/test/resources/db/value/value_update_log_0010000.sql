CREATE OR REPLACE FUNCTION value_update_log_0010000
    (_group_id text) RETURNS void AS
$$
BEGIN

    UPDATE values
       SET last_update = Now()
     WHERE group_id = _group_id;

END
$$ LANGUAGE plpgsql;

PREPARE value_update_log_0010000_call
        (text) AS
SELECT value_update_log_0010000
        ($1);

SELECT value_update_log_0010000
       ('06+1Z+1b+27+29+2P+2Y+'::text);