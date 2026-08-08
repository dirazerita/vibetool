package id.vibetool.app.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.interaction.collectIsPressedAsState
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.scale
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Shape
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import id.vibetool.app.ui.theme.BevelInset
import id.vibetool.app.ui.theme.BevelRaised
import id.vibetool.app.ui.theme.GradientButtonGloss
import id.vibetool.app.ui.theme.ShadowAmbient
import id.vibetool.app.ui.theme.ShadowSpot
import id.vibetool.app.ui.theme.SurfaceInset
import id.vibetool.app.ui.theme.SurfaceRaised
import id.vibetool.app.ui.theme.TextMuted
import java.text.NumberFormat
import java.util.Locale

/** Format angka menjadi "Rp 1.500.000". */
fun rupiah(amount: Double): String {
    val nf = NumberFormat.getNumberInstance(Locale("id", "ID"))
    nf.maximumFractionDigits = 0
    return "Rp " + nf.format(amount)
}

// ===== Modifier skeuomorphic — bahasa visual utama app =====

/** Pelat timbul: bayangan jatuh ke bawah + permukaan bergradasi + bevel rim. */
fun Modifier.skeuoRaised(shape: Shape, elevation: Dp = 10.dp): Modifier = this
    .shadow(elevation, shape, ambientColor = ShadowAmbient, spotColor = ShadowSpot)
    .background(SurfaceRaised, shape)
    .border(1.dp, BevelRaised, shape)

/** Sumur cekung: seolah dicungkil dari permukaan — untuk field, chip, area detail. */
fun Modifier.skeuoInset(shape: Shape): Modifier = this
    .background(SurfaceInset, shape)
    .border(1.dp, BevelInset, shape)

/** Tombol utama glossy 3D — terlihat bisa ditekan, dan memang "tenggelam" saat ditekan. */
@Composable
fun GradientButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    enabled: Boolean = true,
    loading: Boolean = false,
) {
    val shape = RoundedCornerShape(14.dp)
    val interaction = remember { MutableInteractionSource() }
    val pressed by interaction.collectIsPressedAsState()
    val active = enabled && !loading

    Box(
        modifier = modifier
            .scale(if (pressed && active) 0.97f else 1f)
            .shadow(
                if (pressed && active) 2.dp else 10.dp,
                shape,
                ambientColor = ShadowAmbient,
                spotColor = Color(0x994F46E5),
            )
            .background(GradientButtonGloss, shape, alpha = if (active) 1f else 0.55f)
            .border(1.dp, BevelRaised, shape)
            .clickable(
                interactionSource = interaction,
                indication = null,
                enabled = active,
            ) { onClick() }
            .padding(vertical = 15.dp, horizontal = 24.dp),
        contentAlignment = Alignment.Center,
    ) {
        if (loading) {
            CircularProgressIndicator(
                color = Color.White,
                strokeWidth = 2.5.dp,
                modifier = Modifier.size(20.dp),
            )
        } else {
            Text(
                text = text,
                color = Color.White,
                fontWeight = FontWeight.Bold,
                style = MaterialTheme.typography.labelLarge,
            )
        }
    }
}

/** Kartu pelat timbul dengan bevel — bahasa visual utama app. */
@Composable
fun GlassCard(
    modifier: Modifier = Modifier,
    contentPadding: PaddingValues = PaddingValues(16.dp),
    onClick: (() -> Unit)? = null,
    content: @Composable () -> Unit,
) {
    var m = modifier.skeuoRaised(RoundedCornerShape(18.dp))
    if (onClick != null) m = m.clickable { onClick() }

    Box(modifier = m.padding(contentPadding)) {
        content()
    }
}

/** Loading tengah layar. */
@Composable
fun CenterLoading(modifier: Modifier = Modifier) {
    Box(modifier = modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
        CircularProgressIndicator()
    }
}

/** Pesan error / kosong di tengah layar. */
@Composable
fun CenterMessage(message: String, modifier: Modifier = Modifier) {
    Box(modifier = modifier.fillMaxSize().padding(32.dp), contentAlignment = Alignment.Center) {
        Column(
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center,
        ) {
            Text(
                text = message,
                color = TextMuted,
                textAlign = TextAlign.Center,
                style = MaterialTheme.typography.bodyMedium,
                modifier = Modifier.fillMaxWidth(),
            )
        }
    }
}
