# VibeTool Mobile (Android)

Capacitor-based Android wrapper for [vibetool.id](https://vibetool.id).
Hybrid app — native shell with a WebView pointing at the live website,
plus selected native plugins for the in-app experience.

## Status

- **Approach**: Hybrid (Capacitor 6) — WebView shell loads `https://vibetool.id`
- **Distribution**: APK direct download (no Play Store)
- **App ID**: `id.vibetool.app`
- **App name**: VibeTool

## Native plugins enabled

- `@capacitor/app` — handle Android back button, deep links
- `@capacitor/browser` — open external links in Chrome Custom Tabs
- `@capacitor/camera` — capture photo for upload (e.g., bukti transfer)
- `@capacitor/network` — detect offline state
- `@capacitor/share` — share referral link to WhatsApp/etc
- `@capacitor/splash-screen` — branded splash on launch
- `@capacitor/status-bar` — dark status bar matching app theme

## Build prerequisites

- Node.js 20+
- Java JDK 17
- Android SDK (`build-tools;34.0.0`, `platforms;android-34`, `platform-tools`)
- `ANDROID_HOME` environment variable set

## Install dependencies

```bash
cd mobile
npm install
```

## Build debug APK

Output: `android/app/build/outputs/apk/debug/app-debug.apk`

```bash
npm run build:apk
```

## Build release APK (requires signing keystore)

1. Generate a keystore (one-time, keep this file SAFE — losing it means
   you cannot push updates that match the same package signature):

   ```bash
   keytool -genkey -v \
     -keystore android/keystore.jks \
     -alias vibetool \
     -keyalg RSA -keysize 2048 -validity 10000
   ```

2. Create `android/key.properties` (gitignored):

   ```properties
   storePassword=YOUR_KEYSTORE_PASSWORD
   keyPassword=YOUR_KEY_PASSWORD
   keyAlias=vibetool
   storeFile=keystore.jks
   ```

3. Configure `android/app/build.gradle` to read from `key.properties`
   (see Capacitor docs for the standard signingConfig block).

4. Build:

   ```bash
   npm run build:apk-release
   ```

   Output: `android/app/build/outputs/apk/release/app-release.apk`

## Regenerate icons & splash

Source images live in `resources/`. To regenerate Android assets after
updating any source:

```bash
npx capacitor-assets generate --android
```

## Sync after dependency or config changes

```bash
npx cap sync android
```

## How the WebView wrapper works

`capacitor.config.json` has `server.url = https://vibetool.id`, so on
launch the WebView loads the live site directly. The bundled
`web/index.html` is only served as a fallback when the device is offline.

The WebView sends the user-agent suffix `VibeToolApp/0.1.0`, which the
Laravel backend can detect (e.g., to hide certain elements when accessed
from the app) via `request()->userAgent()`.

## Future iterations

- Push notifications via Firebase Cloud Messaging
- Native bottom navigation overlay (Home / Produk / Pesan / Lisensi / Profile)
- Deep link handling (open `https://vibetool.id/...` URLs in app)
- Biometric login
- iOS variant (Capacitor supports the same codebase)
