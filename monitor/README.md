# 🔍 Jeedom Monitor

Scénario de surveillance de tous les équipements Jeedom. Envoie une notification Telegram structurée avec les équipements hors ligne, sans contact et les batteries faibles.

## 📋 Fonctionnalités

- 🔴 **Détection hors ligne** — équipements avec `online = 0` confirmée par l'absence de communication
- ⚠️ **Sans contact** — classement en 3 catégories (jaune / orange / rouge) selon la durée
- 🔋 **Batteries faibles** — détection des niveaux bas et des piles âgées
- ✅ **Filtre battery::disable** — respecte le paramètre "cet équipement n'a pas de batterie"
- 📱 **Notification Telegram** formatée avec emojis et sections claires

## 📱 Exemple de notification

```
🚨 JEEDOM MONITOR — 29/05/2026 16:31

🔴 HORS LIGNE (1)
  [142] [Chambre Parents][Echo Dot - Parents C°]

⚠️ SANS CONTACT (3)

  🟡 Moins de 12h
    [1401] [Entrée][Store Entrée]

  🟠 12h à 72h
    [947] [Chambre Éléonore][Lum_Chambre_Eleonore]

  🔴 Plus de 72h
    [633] [Aucun][VMC Cuisine]

🔋 BATTERIES (2)
  [1013] [Chambre Éléonore][Temp Chambre Ely] - 0%
  [1012] [Chambre Gabriel][Temp Chambre Gaby] - 15%
```

## 🚀 Installation

### 1. Créer le scénario

Dans Jeedom → **Outils → Scénarios → Nouveau scénario** :

| Paramètre | Valeur |
|---|---|
| Nom | `[MONITOR] Surveillance équipements` |
| Mode | `Programmé` |
| Fréquence | `0 */6 * * *` (toutes les 6h) |

### 2. Ajouter le bloc Code

Ajoute un bloc **"Code"** et colle le contenu de [scenario.php](./scenario.php).

### 3. Configurer

En tête du scénario, adapte ces variables :

```php
$seuil_contact_jaune  = 4;    // Heures avant alerte jaune
$seuil_contact_orange = 12;   // Heures avant alerte orange
$seuil_contact_rouge  = 72;   // Heures avant alerte rouge
$seuil_batterie       = 20;   // Pourcentage batterie faible
$seuil_pile_preventif = 300;  // Jours avant alerte pile à changer
$commande_notification = '#[TON_OBJET][TON_EQUIPEMENT][TA_COMMANDE]#';
```

### 4. Exclure les équipements sans batterie

Pour les équipements réseau (Ping, Virtual, etc.) qui ne devraient pas apparaître dans la section Batteries, coche **"Cet équipement n'a pas de batterie"** dans l'onglet **Alertes** de chaque équipement.

Pour le faire en masse via SSH (exemple pour le plugin Networks) :

```bash
mysql -u jeedom -pTON_MOT_DE_PASSE jeedom -e "UPDATE eqLogic SET configuration = JSON_SET(configuration, '$.\"battery::disable\"', '1') WHERE eqType_name = 'networks';"
```

Remplace `networks` par le nom du plugin souhaité (`virtual`, `mqtt2`, etc.).

## ⚙️ Fréquence recommandée

| Fréquence | Cron | Usage |
|---|---|---|
| Toutes les heures | `0 * * * *` | Installation critique |
| Toutes les 6h | `0 */6 * * *` | Usage standard |
| Une fois par jour | `0 8 * * *` | Rapport quotidien |
