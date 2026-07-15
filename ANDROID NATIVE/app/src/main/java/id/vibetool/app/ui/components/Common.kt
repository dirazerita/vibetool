package id.vibetool.app.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
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
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import id.vibetool.app.ui.theme.BorderSoft
import id.vibetool.app.ui.theme.GradientPrimary
import id.vibetool.app.ui.theme.Surface1
import id.vibetool.app.ui.theme.TextMuted
import java.text.NumberFormat
import java.util.Locale

/** Format angka menjadi "Rp 1.500.000". */
fun rupiah(amount: Double): String {
    val nf = NumberFormat.getNumberInstance(Locale("id", "ID"))
    nf.maximumFractionDigits = 0
    return "Rp " + nf.format(amount)
}

/** Tombol utama dengan latar gradient indigo-violet. */
@Composable
fun GradientButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    enabled: Boolean = true,
    loading: Boolean = false,
) {
    Box(
        modifier = modifier
            .background(
                brush = GradientPrimary,
                shape = RoundedCornerShape(14.dp),
                alpha = if (enabled && !loading) 1f else 0.55f,
            )
            .clickable(enabled = enabled && !loading) { onClick() }
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

/** Kartu permukaan gelap dengan border lembut — bahasa visual utama app. */
@Composable
fun GlassCard(
    modifier: Modifier = Modifier,
    contentPadding: PaddingValues = PaddingValues(16.dp),
    onClick: (() -> Unit)? = null,
    content: @Composable () -> Unit,
) {
    var m = modifier
        .background(Surface1, RoundedCornerShape(18.dp))
        .border(1.dp, BorderSoft, RoundedCornerShape(18.dp))
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
