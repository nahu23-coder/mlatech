<?php
/**
 * Helper mínimo para integrar Checkout Pro de Mercado Pago sin depender
 * de Composer/SDK: arma el JSON y hace la llamada por cURL directo a la API REST.
 *
 * Docs: https://www.mercadopago.com.ar/developers/es/reference/preferences/_checkout_preferences/post
 *
 * IMPORTANTE (modo prueba):
 * Como pidieron simular la compra, cada ítem se manda con unit_price = 0.
 * Mercado Pago puede rechazar preferencias con precio 0 (devuelve "invalid_amount"
 * o similar) según la cuenta/país. Si eso pasa, la solución más simple para
 * seguir probando el flujo es cambiar TEST_UNIT_PRICE por un valor bajo (ej: 1)
 * más abajo, o usar directamente una cuenta y tarjetas de prueba de Mercado Pago.
 */

// Precio que se manda a Mercado Pago por cada producto (modo prueba = 0)
define('MP_TEST_UNIT_PRICE', 0);

/**
 * Crea una preferencia de pago en Mercado Pago a partir de un listado de ítems.
 *
 * @param array $items Lista de ['title' => string, 'quantity' => int]
 * @return array ['ok' => bool, 'init_point' => string|null, 'error' => string|null]
 */
function mp_crear_preferencia(array $items): array
{
    if (empty(MP_ACCESS_TOKEN)) {
        return ['ok' => false, 'init_point' => null, 'error' => 'Falta configurar MP_ACCESS_TOKEN en el .env'];
    }

    $mpItems = array_map(function ($item) {
        return [
            'title'      => $item['title'],
            'quantity'   => (int) $item['quantity'],
            'unit_price' => (float) MP_TEST_UNIT_PRICE,
            'currency_id'=> 'ARS',
        ];
    }, $items);

    $body = [
        'items'      => $mpItems,
        'back_urls'  => [
            'success' => SITE_URL . '/src/views/checkout/success.php',
            'failure' => SITE_URL . '/src/views/checkout/failure.php',
            'pending' => SITE_URL . '/src/views/checkout/pending.php',
        ],
        'auto_return' => 'approved',
    ];

    $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . MP_ACCESS_TOKEN,
        ],
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['ok' => false, 'init_point' => null, 'error' => 'No se pudo conectar con Mercado Pago: ' . $curlError];
    }

    $data = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300 && isset($data['init_point'])) {
        // sandbox_init_point sirve para probar con usuarios/tarjetas de prueba
        $initPoint = $data['sandbox_init_point'] ?? $data['init_point'];
        return ['ok' => true, 'init_point' => $initPoint, 'error' => null];
    }

    $mensaje = $data['message'] ?? 'Mercado Pago rechazó la preferencia (HTTP ' . $httpCode . ')';
    return ['ok' => false, 'init_point' => null, 'error' => $mensaje];
}
