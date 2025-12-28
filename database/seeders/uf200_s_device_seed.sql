-- Seed data for ZKTeco UF200-S Device
-- Run this SQL directly in your database if you prefer SQL over Laravel Seeder

INSERT INTO `attendance_devices` (
    `name`,
    `device_id`,
    `device_type`,
    `model`,
    `manufacturer`,
    `serial_number`,
    `location_id`,
    `ip_address`,
    `mac_address`,
    `port`,
    `connection_type`,
    `connection_config`,
    `is_active`,
    `is_online`,
    `last_sync_at`,
    `sync_interval_minutes`,
    `capabilities`,
    `settings`,
    `notes`,
    `created_at`,
    `updated_at`
) VALUES (
    'UF200-S',
    'UF200-S-TRU7251200134',
    'biometric',
    'UF200-S',
    'ZKTECO CO., LTD.',
    'TRU7251200134',
    NULL,
    '192.168.100.127',
    '00:17:61:10:50:2d',
    4370,
    'network',
    '{"subnet_mask":"255.255.255.0","gateway":"192.168.100.1","dns":"0.0.0.0","dhcp":true}',
    1,
    0,
    NULL,
    5,
    '{"fingerprint_algorithm":"ZKFinger VX10.0","face_algorithm":"ZKFace VX7.0","platform":"ZLM60_TFT","supports_fingerprint":true,"supports_face_recognition":true}',
    '{"zkbio_server_ip":null,"zkbio_db_type":"mysql","zkbio_db_host":"192.168.100.109","zkbio_db_name":"ofisi","zkbio_db_user":"root","zkbio_db_password":null,"zkbio_db_path":null}',
    'ZKTeco UF200-S Biometric Device - Serial: TRU7251200134',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `ip_address` = VALUES(`ip_address`),
    `mac_address` = VALUES(`mac_address`),
    `port` = VALUES(`port`),
    `connection_config` = VALUES(`connection_config`),
    `capabilities` = VALUES(`capabilities`),
    `updated_at` = NOW();






