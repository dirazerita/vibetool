package id.vibetool.app.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color

// ===== Palet VibeTool — dark premium (indigo/violet aurora) =====
val Indigo = Color(0xFF6366F1)
val Violet = Color(0xFF8B5CF6)
val IndigoLight = Color(0xFF818CF8)
val BgDeep = Color(0xFF070B17)
val Surface1 = Color(0xFF121A2E)
val Surface2 = Color(0xFF1A2338)
val BorderSoft = Color(0x24818CF8)
val TextPrimary = Color(0xFFF1F5F9)
val TextSecondary = Color(0xFFCBD5E1)
val TextMuted = Color(0xFF94A3B8)
val Green = Color(0xFF34D399)
val Red = Color(0xFFF87171)
val Amber = Color(0xFFF59E0B)

val GradientPrimary = Brush.linearGradient(listOf(Color(0xFF4F46E5), Color(0xFF7C3AED)))
val GradientHero = Brush.linearGradient(listOf(Color(0xFF1E1B4B), Color(0xFF4C1D95)))

// ===== Skeuomorphic tokens — cahaya dari atas, bayangan ke bawah =====

/** Bayangan luar untuk elemen timbul. */
val ShadowAmbient = Color(0x66000000)
val ShadowSpot = Color(0xCC000000)

/** Permukaan timbul: sisi atas kena cahaya, sisi bawah menggelap. */
val SurfaceRaised = Brush.verticalGradient(
    listOf(Color(0xFF212B4A), Color(0xFF161F38), Color(0xFF101728)),
)

/** Permukaan cekung (sumur/inset): gelap di atas, memantul terang di bawah. */
val SurfaceInset = Brush.verticalGradient(
    listOf(Color(0xFF060A15), Color(0xFF0D1426), Color(0xFF141D33)),
)

/** Bevel timbul: rim terang di atas → rim gelap di bawah. */
val BevelRaised = Brush.verticalGradient(
    0f to Color(0x59AAB6FF),
    0.35f to Color(0x1F818CF8),
    1f to Color(0x8A000000),
)

/** Bevel cekung (kebalikan raised): rim gelap di atas → rim terang di bawah. */
val BevelInset = Brush.verticalGradient(
    0f to Color(0xB3000000),
    0.65f to Color(0x14818CF8),
    1f to Color(0x4DAAB6FF),
)

/** Tombol glossy indigo: shine di atas, hard-stop di tengah, dalam di bawah. */
val GradientButtonGloss = Brush.verticalGradient(
    0f to Color(0xFF9BA3FF),
    0.08f to Color(0xFF7C82F5),
    0.5f to Color(0xFF5B54E8),
    0.52f to Color(0xFF4E42DC),
    1f to Color(0xFF3B2FA8),
)

/** Hero glossy: violet dalam dengan pantulan cahaya di sepertiga atas. */
val GradientHeroGloss = Brush.verticalGradient(
    0f to Color(0xFF4C3FA8),
    0.35f to Color(0xFF352A7E),
    0.37f to Color(0xFF2C2168),
    1f to Color(0xFF1A123F),
)

/** Latar layar: bukan warna flat, tapi ruangan dengan cahaya dari atas. */
val GradientScreen = Brush.verticalGradient(
    listOf(Color(0xFF0D1326), BgDeep, Color(0xFF04060E)),
)

private val VibeToolColorScheme = darkColorScheme(
    primary = Indigo,
    onPrimary = Color.White,
    secondary = Violet,
    onSecondary = Color.White,
    tertiary = IndigoLight,
    background = BgDeep,
    onBackground = TextPrimary,
    surface = Surface1,
    onSurface = TextPrimary,
    surfaceVariant = Surface2,
    onSurfaceVariant = TextSecondary,
    outline = Color(0x33818CF8),
    error = Red,
)

@Composable
fun VibeToolTheme(content: @Composable () -> Unit) {
    MaterialTheme(
        colorScheme = VibeToolColorScheme,
        typography = AppTypography,
        content = content,
    )
}
