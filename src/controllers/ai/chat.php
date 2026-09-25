<?php
require_once __DIR__ . '/../../config/bootstrap.php';

header('Content-Type: application/json');

$mensaje = trim($_POST['message'] ?? '');

if ($mensaje === '') {
    echo json_encode(['reply' => 'Escribime tu consulta y te ayudo.']);
    exit;
}

/**
 * Modo 1: sin API key configurada -> respondemos con un bot de preguntas
 * frecuentes basado en palabras clave. Funciona sin depender de ningún
 * servicio externo, ideal para la entrega del proyecto.
 */
function respuestaFaq(string $mensaje): ?string
{
    $m = mb_strtolower($mensaje);

    $faq = [
        ['keywords' => ['envio', 'envío', 'entrega', 'demora'],
         'respuesta' => 'Hacemos envíos a todo el país. El tiempo de entrega estimado es de 2 a 5 días hábiles según tu ubicación.'],
        ['keywords' => ['garantia', 'garantía'],
         'respuesta' => 'Todos nuestros productos tienen garantía oficial de 6 a 12 meses según el fabricante.'],
        ['keywords' => ['pago', 'mercado pago', 'tarjeta', 'cuotas'],
         'respuesta' => 'Podés pagar con Mercado Pago (tarjetas de crédito/débito y otros medios). En esta versión de prueba, el pago se simula sin costo real.'],
        ['keywords' => ['cambio', 'devolucion', 'devolución'],
         'respuesta' => 'Tenés 10 días desde la recepción del producto para solicitar un cambio o devolución, siempre que esté sin uso.'],
        ['keywords' => ['stock', 'disponible', 'hay'],
         'respuesta' => 'Podés ver el stock disponible de cada producto en su ficha, dentro de la sección Productos.'],
        ['keywords' => ['horario', 'atencion', 'atención', 'contacto', 'humano'],
         'respuesta' => 'Nuestro equipo humano atiende de lunes a viernes de 9 a 18hs. También podés escribirnos desde la sección Contacto.'],
        ['keywords' => ['hola', 'buenas', 'buenos dias', 'buenas tardes'],
         'respuesta' => '¡Hola! ¿En qué te puedo ayudar? Puedo responderte sobre envíos, garantía, pagos y productos.'],
    ];

    foreach ($faq as $entry) {
        foreach ($entry['keywords'] as $keyword) {
            if (str_contains($m, $keyword)) {
                return $entry['respuesta'];
            }
        }
    }

    return null;
}

/**
 * Modo 2: si hay una AI_API_KEY configurada en el .env, usamos la API de
 * Claude (Anthropic) para responder de forma más flexible. Si esta llamada
 * falla por cualquier motivo, caemos al modo FAQ como respaldo.
 */
function respuestaIA(string $mensaje): ?string
{
    if (empty(AI_API_KEY)) {
        return null;
    }

    $systemPrompt = 'Sos el asistente de soporte de MLA Tech, una tienda online de componentes de PC, '
        . 'periféricos y accesorios. Respondé en español, de forma breve y amable (máximo 3 líneas). '
        . 'Si te preguntan algo que no tiene que ver con la tienda, respondé igual con amabilidad pero '
        . 'aclarando que sos un asistente de soporte de MLA Tech.';

    $body = [
        'model'      => 'claude-haiku-4-5-20251001',
        'max_tokens' => 300,
        'system'     => $systemPrompt,
        'messages'   => [
            ['role' => 'user', 'content' => $mensaje],
        ],
    ];

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'x-api-key: ' . AI_API_KEY,
            'anthropic-version: 2023-06-01',
            'content-type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_TIMEOUT    => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return null;
    }

    $data = json_decode($response, true);
    return $data['content'][0]['text'] ?? null;
}

$respuesta = respuestaIA($mensaje) ?? respuestaFaq($mensaje) ?? (
    'No tengo una respuesta puntual para eso, pero podés escribirnos desde la sección Contacto '
    . 'y nuestro equipo te responde a la brevedad.'
);

echo json_encode(['reply' => $respuesta]);
