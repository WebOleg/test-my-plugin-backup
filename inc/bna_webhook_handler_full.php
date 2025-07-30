<?php
if (!defined('ABSPATH')) exit;


add_action('init', 'bna_handle_webhook');

function bna_handle_webhook() {
    if (!isset($_GET['wc-api']) || $_GET['wc-api'] !== 'bna_pay') {
        return;
    }

    // Get and validate JSON
    $raw_input = file_get_contents('php://input');
    $payload = json_decode($raw_input, true);

    if (!is_array($payload) || !isset($payload['data'])) {
        http_response_code(400);
        exit('Invalid payload structure');
    }

    // Validate secret
    $settings = get_option('woocommerce_bna_gateway_settings');
    $expected_secret = $settings['webhook_secret'] ?? '';
    if (empty($_GET['secret']) || $_GET['secret'] !== $expected_secret) {
        http_response_code(403);
        exit('Invalid secret');
    }

    $endpoint = $_GET['endpoint'] ?? '';
    $data = $payload['data'];

    switch ($endpoint) {
        case 'customers':
            bna_process_customer($data['customer'] ?? []);
            break;

        case 'subscriptions':
            bna_process_subscription($data['subscription'] ?? [], $data['customer'] ?? []);
            break;

        case 'transactions':
            bna_process_transaction($data['transaction'] ?? [], $data['customer'] ?? []);
            break;

        default:
            http_response_code(400);
            exit('Unknown endpoint');
    }

    http_response_code(200);
    echo 'OK';
    exit;
}

function bna_process_customer(array $data) {
    global $wpdb;
    $table = $wpdb->prefix . 'bna_customers';

    if (empty($data['id']) || empty($data['type']) || empty($data['address']['postalCode'])) {
        return;
    }

    $wpdb->replace($table, [
        'id'              => $data['id'],
        'type'            => strtoupper($data['type']),
        'email'           => $data['email'] ?? '',
        'phone_code'      => $data['phoneCode'] ?? '',
        'phone_number'    => $data['phoneNumber'] ?? '',
        'first_name'      => $data['firstName'] ?? '',
        'last_name'       => $data['lastName'] ?? '',
        'birth_date'      => !empty($data['birthDate']) ? date('Y-m-d', strtotime($data['birthDate'])) : null,
        'postal_code'     => $data['address']['postalCode'],
        'additional_info' => isset($data['additionalInfo']) ? json_encode($data['additionalInfo']) : null
    ]);
}


function bna_process_subscription(array $sub, array $customer = []) {
    global $wpdb;
    $table = $wpdb->prefix . 'bna_subscriptions';

    if (empty($sub['id']) || empty($customer['address']['postalCode'])) {
        return;
    }

    $wpdb->replace($table, [
        'id'                  => $sub['id'],
        'customer_id'         => $sub['customerId'] ?? '',
        'type'                => strtoupper($customer['type'] ?? ''),
        'email'               => $customer['email'] ?? '',
        'phone_code'          => $customer['phoneCode'] ?? '',
        'phone_number'        => $customer['phoneNumber'] ?? '',
        'first_name'          => $customer['firstName'] ?? '',
        'last_name'           => $customer['lastName'] ?? '',
        'postal_code'         => $customer['address']['postalCode'],
        'recurrence'          => $sub['recurrence'] ?? '',
        'currency'            => $sub['currency'] ?? '',
        'start_payment_date'  => !empty($sub['startPaymentDate']) ? date('Y-m-d', strtotime($sub['startPaymentDate'])) : null,
        'last_payment_date'   => !empty($sub['lastPaymentDate']) ? date('Y-m-d', strtotime($sub['lastPaymentDate'])) : null,
        'next_payment_date'   => !empty($sub['nextPaymentDate']) ? date('Y-m-d', strtotime($sub['nextPaymentDate'])) : null,
        'remaining_payments'  => $sub['remainingPayments'] ?? null,
        'status'              => $sub['status'] ?? '',
        'management_url'      => $sub['managementUrl'] ?? ''
    ]);
}


function bna_process_transaction(array $trx, array $customer = []) {
    global $wpdb;
    $table = $wpdb->prefix . 'bna_transactions';

    if (empty($trx['referenceUUID'])) {
        return;
    }

    $wpdb->insert($table, [
        'referenceUUID'   => $trx['referenceUUID'],
        'customerId'      => $trx['customerId'] ?? '',
        'subscriptionId'  => $trx['subscriptionId'] ?? '',
        'json'            => json_encode(['transaction' => $trx, 'customer' => $customer]),
        'status'          => $trx['status'] ?? '',
        'created_at'      => current_time('mysql')
    ]);
}
