CREATE TABLE daily_tasks (
id int NOT NULL AUTO_INCREMENT,
user_id text COLLATE utf8mb4_general_ci NOT NULL,
task_description text COLLATE utf8mb4_general_ci,
is_completed tinyint(1) DEFAULT '0',
task_date date NOT NULL DEFAULT (curdate()),
priority int DEFAULT '0' COMMENT '1=Urgent, 2=High, 3=Medium, 4=Low, 5=Optional',
remarks text COLLATE utf8mb4_general_ci,
time text COLLATE utf8mb4_general_ci,
PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
