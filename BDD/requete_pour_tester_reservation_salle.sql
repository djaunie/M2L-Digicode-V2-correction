INSERT INTO `mrbs_entry` (
    `start_time`, `end_time`, `entry_type`, `repeat_id`, `room_id`,
    `create_by`, `name`, `type`, `description`, `status`,
    `reminded`, `info_time`, `info_user`, `info_text`,
    `ical_uid`, `ical_sequence`, `ical_recur_id`
)
VALUES (
    UNIX_TIMESTAMP(CONCAT(CURDATE(), ' 09:00:00')),
    UNIX_TIMESTAMP(CONCAT(CURDATE(), ' 11:00:00')),
    0, 0, 1,
    'cheminl', 'Réunion de cheminl', 'E', '', 0,
    NULL, NULL, NULL, NULL, '', 0, ''
);