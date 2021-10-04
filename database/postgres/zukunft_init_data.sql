--
-- Database: 'zukunft' - loading of predefined code linked database records
--


--
-- Setting the initial IP blocking for testing
--

INSERT INTO user_blocked_ips (user_blocked_id, ip_from, ip_to, reason, is_active) VALUES
    (1, '66.249.64.95', '66.249.64.95', 'too much damage from this IP', 1);

