<?php
// ============================================================
// JEEDOM HEARTBEAT - Surveillance externe via Healthchecks.io
// Version 1.0
// GitHub : https://github.com/Alweddle/jeedom-admin-toolkit
// ============================================================
// CONFIGURATION - Remplacer l'URL par votre URL Healthchecks.io
// ============================================================

$url = 'https://hc-ping.com/VOTRE-URL-ICI';

// ============================================================
// NE PAS MODIFIER EN DESSOUS DE CETTE LIGNE
// ============================================================

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_exec($ch);
curl_close($ch);
