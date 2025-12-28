-- Drop unused Visitor Management System tables
-- These tables are not referenced anywhere in the codebase
-- Generated: 2025-11-24

SET FOREIGN_KEY_CHECKS = 0;

-- Visitor Management System Tables (Unused)
DROP TABLE IF EXISTS `visitor_reason`;
DROP TABLE IF EXISTS `visitor_reservation`;
DROP TABLE IF EXISTS `visitor_visitor`;
DROP TABLE IF EXISTS `visitor_visitorbiodata`;
DROP TABLE IF EXISTS `visitor_visitorbiophoto`;
DROP TABLE IF EXISTS `visitor_visitorconfig`;
DROP TABLE IF EXISTS `visitor_visitorlog`;
DROP TABLE IF EXISTS `visitor_visitortransaction`;
DROP TABLE IF EXISTS `visitor_visitor_acc_groups`;
DROP TABLE IF EXISTS `visitor_visitor_area`;

-- Workflow Engine System Tables (Unused)
DROP TABLE IF EXISTS `workflow_nodeinstance`;
DROP TABLE IF EXISTS `workflow_workflowengine`;
DROP TABLE IF EXISTS `workflow_workflowengine_employee`;
DROP TABLE IF EXISTS `workflow_workflowinstance`;
DROP TABLE IF EXISTS `workflow_workflownode`;
DROP TABLE IF EXISTS `workflow_workflownode_approver`;
DROP TABLE IF EXISTS `workflow_workflownode_notifier`;
DROP TABLE IF EXISTS `workflow_workflowrole`;

SET FOREIGN_KEY_CHECKS = 1;

-- Note: work_schedules table is KEPT because it's actively used in:
-- - Attendance system (foreign key reference)
-- - AttendanceSettingsController
-- - Has migration: 2025_11_10_101656_create_work_schedules_table.php

