<?php
include 'db_connect.php';

// Lee raw POST data
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Verifica con PayPal (usa sandbox URL para test)
$req = 'cmd=_notify-validate';
$req .= '&' . http_build_query($myPost);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr');  // Sandbox; cambia a ipnpb.paypal.com para live
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$res = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if (!$error && strcmp($res, "VERIFIED") == 0 && $myPost['payment_status'] == 'Completed' && $myPost['mc_gross'] == '1.00') {
    $custom_email = $myPost['custom'];  // Email user del botón
    $new_expire = date('Y-m-d H:i:s', strtotime('+1 month'));
    $update_query = "UPDATE users SET subscription_active = '$new_expire' WHERE email = '$custom_email'";
    mysqli_query($conn, $update_query);
}
?>