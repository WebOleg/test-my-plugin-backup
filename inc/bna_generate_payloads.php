<?php
if (!defined('ABSPATH')) exit;

add_action('admin_init', 'bna_register_generate_payload_action');
add_action('admin_notices', 'bna_generate_payload_notice');
add_action('woocommerce_admin_field_bna_generate_payloads_button', 'bna_render_generate_payload_button');

function bna_register_generate_payload_action() {
    if (isset($_GET['bna-generate-payloads']) && current_user_can('manage_woocommerce')) {
        bna_generate_test_payload_files();
        wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=bna_gateway&payloads_generated=true'));
        exit;
    }
}

function bna_generate_payload_notice() {
    if (isset($_GET['payloads_generated'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Webhook test payloads successfully generated in <code>wp-content/uploads/bna_webhook_samples/</code>.</p></div>';
    }
}

function bna_render_generate_payload_button() {
    $url = admin_url('admin.php?page=wc-settings&tab=checkout&section=bna_gateway&bna-generate-payloads=1');
    echo '<tr valign="top">
        <th scope="row">Generate Test Payloads</th>
        <td>
            <a href="' . esc_url($url) . '" class="button button-primary">Generate Payloads</a>
        </td>
    </tr>';
}

function bna_generate_test_payload_files() {
    $upload_dir = wp_upload_dir();
    $path = trailingslashit($upload_dir['basedir']) . 'bna_webhook_samples';
    if (!file_exists($path)) {
        wp_mkdir_p($path);
    }

    $payloads = [
        'customer.json' => [
            "event" => "customer.created",
            "deliveryId" => "test-delivery-001",
            "configId" => "cfg-001",
            "data" => [
                "customer" => [
                    "id" => "cus_1001",
                    "email" => "john@example.com",
                    "phoneCode" => "+1",
                    "phoneNumber" => "5551234567",
                    "firstName" => "John",
                    "lastName" => "Doe",
                    "type" => "PERSONAL",
                    "birthDate" => "1990-01-15",
                    "address" => [
                        "country" => "US",
                        "province" => "CA",
                        "city" => "Los Angeles",
                        "streetName" => "Sunset Blvd",
                        "streetNumber" => "101",
                        "apartment" => "12B",
                        "postalCode" => "90001"
                    ],
                    "additionalInfo" => [
                        "field1" => "VIP",
                        "field2" => "test"
                    ]
                ]
            ]
        ],
        'transaction.json' => [
            "event" => "transaction.created",
            "deliveryId" => "test-delivery-002",
            "configId" => "cfg-002",
            "data" => [
                "transaction" => [
                    "transactionUUID" => "trx_2001",
                    "customerId" => "cus_1001",
                    "referenceUUID" => "ref_abc123",
                    "subscriptionId" => "sub_3001",
                    "transactionTime" => "2025-07-29T13:00:00Z",
                    "paymentMethod" => "credit_card",
                    "status" => "approved",
                    "action" => "PAYMENT",
                    "currency" => "USD",
                    "fee" => 1.5,
                    "subtotal" => 10,
                    "total" => 11.5,
                    "amount" => 11.5,
                    "balance" => 0,
                    "paymentDetails" => "**** **** **** 1234",
                    "invoiceInfo" => "Invoice #123",
                    "contractInfo" => "Contract A",
                    "transactionComment" => "First payment",
                    "items" => ["item1", "item2"],
                    "authCode" => "AUTH123",
                    "interacUrl" => "https://interac.example.com",
                    "interacReference" => "INT123",
                    "metadata" => ["orderId" => "woocom_789"]
                ],
                "customer" => [
                    "id" => "cus_1001",
                    "type" => "PERSONAL",
                    "address" => ["postalCode" => "90001"]
                ]
            ]
        ],
        'subscription.json' => [
            "event" => "subscription.created",
            "deliveryId" => "test-delivery-003",
            "configId" => "cfg-003",
            "data" => [
                "subscription" => [
                    "id" => "sub_3001",
                    "customerId" => "cus_1001",
                    "recurrence" => "MONTHLY",
                    "action" => "START",
                    "status" => "ACTIVE",
                    "subtotal" => 20,
                    "currency" => "USD",
                    "applyFee" => 2,
                    "startPaymentDate" => "2025-07-29",
                    "lastPaymentDate" => "2025-07-29",
                    "nextPaymentDate" => "2025-08-29",
                    "remainingPayments" => 11,
                    "paymentMethod" => "credit_card",
                    "paymentDetails" => "**** **** **** 5678",
                    "invoiceInfo" => "INV-001",
                    "contractInfo" => "CONT-001",
                    "items" => ["service1"],
                    "comment" => "Monthly Plan",
                    "metadata" => ["woocommerce_user" => "42"]
                ],
                "customer" => [
                    "id" => "cus_1001",
                    "email" => "john@example.com",
                    "phoneCode" => "+1",
                    "phoneNumber" => "5551234567",
                    "firstName" => "John",
                    "lastName" => "Doe",
                    "type" => "PERSONAL",
                    "address" => ["postalCode" => "90001"]
                ]
            ]
        ]
    ];

    foreach ($payloads as $file => $content) {
        file_put_contents("$path/$file", json_encode($content, JSON_PRETTY_PRINT));
    }
}
