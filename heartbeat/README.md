# 💓 Jeedom Heartbeat

Surveillance externe de Jeedom via [Healthchecks.io](https://healthchecks.io). Envoie un ping toutes les 5 minutes — si Jeedom tombe ou freeze, une alerte est envoyée automatiquement.

## 📋 Fonctionnalités

- 🟢 **Surveillance externe** — Healthchecks.io surveille Jeedom depuis l'extérieur
- 📱 **Alertes Telegram** — notification via votre propre bot Telegram
- 📧 **Alertes Email** — notification par email en complément
- ⚡ **Détection rapide** — alerte en 15 minutes maximum après une panne

## 🚀 Installation

### 1. Créer un compte Healthchecks.io

1. Va sur [healthchecks.io](https://healthchecks.io) et crée un compte gratuit
2. Clique **"Add Check"** et configure :

| Paramètre | Valeur |
|---|---|
| Name | `Jeedom Heartbeat` |
| Period | `5 minutes` |
| Grace Time | `10 minutes` |

3. Copie l'**URL de ping** générée (`https://hc-ping.com/XXXX`)

### 2. Configurer les alertes Telegram

Dans Healthchecks.io → **Integrations → Add Integration → Webhook** :

**Execute when a check goes DOWN :**
- Method : `POST`
- URL : `https://api.telegram.org/botTON_TOKEN/sendMessage`
- Headers : `Content-Type: application/json`
- Body :
```json
{"chat_id":TON_CHAT_ID,"parse_mode":"HTML","text":"🚨 <b>JEEDOM HORS LIGNE</b>\n\n📛 Check : <code>$NAME</code>\n🕐 Date : $NOW\n\n⚠️ Vérifiez votre installation !"}
```

**Execute when a check goes UP :**
- Method : `POST`
- URL : `https://api.telegram.org/botTON_TOKEN/sendMessage`
- Headers : `Content-Type: application/json`
- Body :
```json
{"chat_id":TON_CHAT_ID,"parse_mode":"HTML","text":"✅ <b>JEEDOM EST DE RETOUR</b>\n\n📛 Check : <code>$NAME</code>\n🕐 Retour en ligne : $NOW\n\n💚 Tout est normal."}
```

### 3. Trouver votre Chat ID Telegram

Envoyez un message à votre bot puis ouvrez dans le navigateur :
```
https://api.telegram.org/botTON_TOKEN/getUpdates
```
Le Chat ID se trouve dans `"chat":{"id": XXXXXXXX}`.

### 4. Créer le scénario Jeedom

Dans Jeedom → **Outils → Scénarios → Nouveau scénario** :

| Paramètre | Valeur |
|---|---|
| Nom | `[MONITOR] Heartbeat Healthchecks` |
| Mode | `Programmé` |
| Fréquence | `*/5 * * * *` |

Ajoute un bloc **"Code"** et colle le contenu de [scenario.php](./scenario.php) en remplaçant l'URL.

## ✅ Test

Désactive le scénario 15 minutes — tu dois recevoir une alerte. Réactive-le — le check repasse au vert.
