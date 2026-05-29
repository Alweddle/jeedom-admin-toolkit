<?php
// ============================================================
// JEEDOM MONITOR - Surveillance equipements
// Version 1.0
// GitHub : https://github.com/Alweddle/jeedom-admin-toolkit
// ============================================================
// CONFIGURATION - Modifier ces valeurs selon vos besoins
// ============================================================

$seuil_contact_jaune  = 4;    // Heures avant alerte jaune
$seuil_contact_orange = 12;   // Heures avant alerte orange
$seuil_contact_rouge  = 72;   // Heures avant alerte rouge
$seuil_batterie       = 20;   // Pourcentage batterie faible
$seuil_pile_preventif = 300;  // Jours avant alerte pile a changer
$commande_notification = '#[TON_OBJET][TON_EQUIPEMENT][TA_COMMANDE]#';

// ============================================================
// NE PAS MODIFIER EN DESSOUS DE CETTE LIGNE
// ============================================================

$eqLogics = eqLogic::all();
$horsLigne = [];
$contact_jaune = [];
$contact_orange = [];
$contact_rouge = [];
$batteriesFaibles = [];
$maintenant = new DateTime();

foreach ($eqLogics as $eqLogic) {
    if (!$eqLogic->getIsEnable()) continue;
    
    $status = $eqLogic->getStatus();
    $id = $eqLogic->getId();
    $nom = mb_convert_encoding($eqLogic->getHumanName(), 'UTF-8', 'UTF-8');
    $label = '[' . $id . '] ' . $nom;
    $config = $eqLogic->getConfiguration();
    
    // Batterie - ignore si battery::disable = 1
    if (empty($config['battery::disable']) || $config['battery::disable'] != '1') {
        
        if (array_key_exists('battery', $status)) {
            $battery = (float)$status['battery'];
            if ($battery == 0) {
                $batteriesFaibles[] = $label . ' - 0%';
            } elseif ($battery < $seuil_batterie) {
                $batteriesFaibles[] = $label . ' - ' . (int)$battery . '%';
            }
        }
        
        // Pile ancienne - alerte preventive
        if (!empty($status['batteryDatetime'])) {
            $datePile = new DateTime($status['batteryDatetime']);
            $diffPile = $maintenant->diff($datePile);
            $jours = $diffPile->days;
            if ($jours >= $seuil_pile_preventif && $jours < 365) {
                $batteriesFaibles[] = $label . ' - A changer bientot (' . $jours . 'j)';
            }
        }
    }
    
    // Hors ligne - online=0 ET pas de contact depuis plus de seuil_contact_jaune heures
    if (isset($status['online']) && (int)$status['online'] == 0) {
        $confirme = true;
        if (!empty($status['lastCommunication'])) {
            $dernierContact = new DateTime($status['lastCommunication']);
            $diff = $maintenant->diff($dernierContact);
            $heures = ($diff->days * 24) + $diff->h;
            if ($heures < $seuil_contact_jaune) {
                $confirme = false;
            }
        }
        if ($confirme) {
            $horsLigne[] = $label;
        }
    }
    
    // Sans contact
    if (!empty($status['lastCommunication'])) {
        $dernierContact = new DateTime($status['lastCommunication']);
        $diff = $maintenant->diff($dernierContact);
        $heures = ($diff->days * 24) + $diff->h;
        
        if ($heures >= $seuil_contact_jaune && $heures < $seuil_contact_orange) {
            $contact_jaune[] = $label;
        } elseif ($heures >= $seuil_contact_orange && $heures < $seuil_contact_rouge) {
            $contact_orange[] = $label;
        } elseif ($heures >= $seuil_contact_rouge) {
            $contact_rouge[] = $label;
        }
    }
}

// Tri alphabetique
sort($horsLigne);
sort($contact_jaune);
sort($contact_orange);
sort($contact_rouge);
sort($batteriesFaibles);

$totalContact = count($contact_jaune) + count($contact_orange) + count($contact_rouge);
$message = "🚨 <b>JEEDOM MONITOR</b> — " . date('d/m/Y H:i') . "\n";

// Hors ligne
$message .= "\n🔴 <b>HORS LIGNE (" . count($horsLigne) . ")</b>\n";
if (count($horsLigne) > 0) {
    foreach ($horsLigne as $item) {
        $message .= "  " . $item . "\n";
    }
} else {
    $message .= "  Ok\n";
}

// Sans contact
$message .= "\n⚠️ <b>SANS CONTACT (" . $totalContact . ")</b>\n";
if ($totalContact > 0) {
    if (count($contact_jaune) > 0) {
        $message .= "\n  🟡 <i>Moins de " . $seuil_contact_orange . "h</i>\n";
        foreach ($contact_jaune as $item) {
            $message .= "    " . $item . "\n";
        }
    }
    if (count($contact_orange) > 0) {
        $message .= "\n  🟠 <i>" . $seuil_contact_orange . "h a " . $seuil_contact_rouge . "h</i>\n";
        foreach ($contact_orange as $item) {
            $message .= "    " . $item . "\n";
        }
    }
    if (count($contact_rouge) > 0) {
        $message .= "\n  🔴 <i>Plus de " . $seuil_contact_rouge . "h</i>\n";
        foreach ($contact_rouge as $item) {
            $message .= "    " . $item . "\n";
        }
    }
} else {
    $message .= "  Ok\n";
}

// Batteries
$message .= "\n🔋 <b>BATTERIES (" . count($batteriesFaibles) . ")</b>\n";
if (count($batteriesFaibles) > 0) {
    foreach ($batteriesFaibles as $item) {
        $message .= "  " . $item . "\n";
    }
} else {
    $message .= "  Ok\n";
}

// Envoi par chunks pour eviter la limite Telegram
$chunks = str_split($message, 3000);
foreach ($chunks as $chunk) {
    cmd::byString($commande_notification)
        ->execCmd(['message' => $chunk, 'title' => 'Jeedom Monitor']);
}
