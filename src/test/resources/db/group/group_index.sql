-- --------------------------------------------------------

--
-- indexes for table groups
--
CREATE INDEX groups_user_idx ON groups (user_id);

--
-- indexes for table user_groups
--
ALTER TABLE user_groups ADD CONSTRAINT user_groups_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_groups_user_idx ON user_groups (user_id);

--
-- indexes for table groups_prime
--
CREATE INDEX groups_prime_user_idx ON groups_prime (user_id);

--
-- indexes for table user_groups_prime
--
ALTER TABLE user_groups_prime ADD CONSTRAINT user_groups_prime_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_groups_prime_user_idx ON user_groups_prime (user_id);

--
-- indexes for table groups_big
--
CREATE INDEX groups_big_user_idx ON groups_big (user_id);

--
-- indexes for table user_groups_big
--
ALTER TABLE user_groups_big ADD CONSTRAINT user_groups_big_pkey PRIMARY KEY (group_id, user_id);
CREATE INDEX user_groups_big_user_idx ON user_groups_big (user_id);
