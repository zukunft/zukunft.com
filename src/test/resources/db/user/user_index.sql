-- --------------------------------------------------------

--
-- indexes for table users
--

CREATE INDEX users_user_name_idx ON users (user_name);
CREATE INDEX users_ip_address_idx ON users (ip_address);
CREATE INDEX users_code_idx ON users (code_id);
CREATE INDEX users_user_profile_idx ON users (user_profile_id);
CREATE INDEX users_user_type_idx ON users (user_type_id);
