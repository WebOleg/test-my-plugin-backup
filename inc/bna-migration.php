<?php
if (!defined('ABSPATH')) exit;

/**
 * Run BNA database migrations for customers, subscriptions, and transactions.
 */
function bna_run_migrations() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Create table: wp_bna_customers
    $wpdb->query("
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}bna_customers (
            id VARCHAR(100) PRIMARY KEY,
            type VARCHAR(20) NOT NULL,
            email VARCHAR(100),
            phone_code VARCHAR(10),
            phone_number VARCHAR(20),
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            birth_date DATE,
            postal_code VARCHAR(20),
            additional_info TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;
    ");

    // Create table: wp_bna_subscriptions
    $wpdb->query("
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}bna_subscriptions (
            id VARCHAR(100) PRIMARY KEY,
            customer_id VARCHAR(100),
            type VARCHAR(20),
            email VARCHAR(100),
            phone_code VARCHAR(10),
            phone_number VARCHAR(20),
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            postal_code VARCHAR(20),
            recurrence VARCHAR(50),
            currency VARCHAR(10),
            start_payment_date DATE,
            last_payment_date DATE,
            next_payment_date DATE,
            remaining_payments INT,
            status VARCHAR(30),
            management_url TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;
    ");

    // Alter table: wp_bna_transactions (conditionally)
    $columns = [
        'referenceUUID VARCHAR(100)',
        'customerId VARCHAR(100)',
        'subscriptionId VARCHAR(100)',
        'json LONGTEXT',
        'status VARCHAR(30)',
        'created_at DATETIME DEFAULT CURRENT_TIMESTAMP'
    ];

    $table_name = $wpdb->prefix . 'bna_transactions';

    foreach ($columns as $column) {
        $column_name = explode(' ', $column)[0];
        $exists = $wpdb->get_results(
            $wpdb->prepare("SHOW COLUMNS FROM `$table_name` LIKE %s", $column_name)
        );

        if (empty($exists)) {
            $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN $column;");
        }
    }
}
