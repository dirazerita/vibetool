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
