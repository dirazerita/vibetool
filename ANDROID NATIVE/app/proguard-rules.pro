# Retrofit + Gson: pertahankan model data agar tidak di-obfuscate
-keep class id.vibetool.app.data.** { *; }
-keepattributes Signature
-keepattributes *Annotation*
-dontwarn okhttp3.**
-dontwarn retrofit2.**
