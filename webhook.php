<?php
// webhook.php - Maneja notificaciones de PayPal (v1 legacy + v2 subscriptions)
// Responde 200 OK siempre; no output visible

include 'db_connect.php';

// Credenciales para verify (opcional para MVP)
$client_id = 'AfESm2xYJl3N-sKBRvIXjnMf96CJxVR-k1PCF3JHfPb_Fq0UrrqIQHziTXgeXXtOA-6Zsymc7jVr221X';  // De My Apps & Credentials > Sandbox
$client_secret = 'EBDeeBUop6VJMPmyeNX5IYJpQc5I4CeRW9qm103dudQu1IWCWA73mWxv2oQgvNC9A9h2XlZI1IajNzZv';  // Guárdalo seguro

// Lee raw POST (JSON de PayPal)
$input = file_get_contents('php://input');
$event = json_decode($input, true);

// Log detallado para debug
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - Event recibido: " . $input . "\n", FILE_APPEND | LOCK_EX);

// Verifica evento básico
if (!$event || !isset($event['event_type']) || $event['event_type'] !== 'BILLING.SUBSCRIPTION.ACTIVATED') {
    file_put_contents('webhook_log.txt', "Error: Evento inválido o no ACTIVATED\n", FILE_APPEND);
    http_response_code(400);
    exit();
}

// Extrae email: Prioridad custom_id, subscriber.email_address (v1/v2), payer.email_address
$custom_id = $event['resource']['custom_id'] ?? '';
$subscriber_email = $event['resource']['subscriber']['email_address'] ?? '';
$payer_email = $event['resource']['payer']['email_address'] ?? '';

$email = $custom_id ?: $subscriber_email ?: $payer_email;

// Log de extracción
file_put_contents('webhook_log.txt', "Email extraído: '$email' (custom: '$custom_id', subscriber: '$subscriber_email', payer: '$payer_email')\n", FILE_APPEND);

if (empty($email)) {
    file_put_contents('webhook_log.txt', "Error: No email encontrado en evento\n", FILE_APPEND);
    http_response_code(400);
    exit();
}

// Procesa ACTIVATED: Setea expire +1 mes
$new_expire = date('Y-m-d H:i:s', strtotime('+1 month'));
$update_query = "UPDATE users SET subscription_active = '$new_expire' WHERE email = '$email'";
$result = mysqli_query($conn, $update_query);
$affected = mysqli_affected_rows($conn);

file_put_contents('webhook_log.txt', "ACTIVATED procesado: $email hasta $new_expire (affected rows: $affected)\n", FILE_APPEND);

// Para otros eventos (agrega si needed)
// if ($event['event_type'] === 'BILLING.SUBSCRIPTION.SUSPENDED') { UPDATE NULL }

http_response_code(200);  // OK a PayPal
?>