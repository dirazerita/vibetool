package id.vibetool.app.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Paid
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.Commission
import id.vibetool.app.ui.components.CenterLoading
import id.vibetool.app.ui.components.CenterMessage
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.components.rupiah
import id.vibetool.app.ui.theme.Amber
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.IndigoLight
import id.vibetool.app.ui.theme.TextMuted

@Composable
fun CommissionsScreen() {
    var commissions by remember { mutableStateOf<List<Commission>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        try {
            val res = ApiClient.api().commissions()
            if (res.isSuccessful) {
                commissions = res.body()?.commissions ?: emptyList()
            } else {
                error = "Gagal memuat komisi."
            }
        } catch (e: Exception) {
            error = "Tidak bisa terhubung ke server."
        }
        loading = false
    }

    when {
        loading -> CenterLoading()
        error != null -> CenterMessage(error!!)
        commissions.isEmpty() -> CenterMessage("Belum ada komisi.\nBagikan link afiliasimu untuk mulai menghasilkan!")
        else -> LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp),
        ) {
            items(commissions, key = { it.id }) { c ->
                CommissionRow(c)
            }
        }
    }
}

@Composable
private fun CommissionRow(c: Commission) {
    val typeColor = when (c.type) {
        "direct" -> Green
        "upline" -> IndigoLight
        else -> Amber
    }
    val typeLabel = when (c.type) {
        "direct" -> "Komisi Langsung"
        "upline" -> "Bonus Upline"
        "creator" -> "Bagian Kreator"
        else -> c.type
    }

    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Box(
                modifier = Modifier
                    .size(42.dp)
                    .background(typeColor.copy(alpha = 0.15f), CircleShape),
                contentAlignment = Alignment.Center,
            ) {
                Icon(Icons.Filled.Paid, contentDescription = null, tint = typeColor, modifier = Modifier.size(22.dp))
            }
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(typeLabel, style = MaterialTheme.typography.titleMedium)
                Text(
                    c.productTitle ?: "—",
                    style = MaterialTheme.typography.bodyMedium,
                    color = TextMuted,
                    maxLines = 1,
                )
                c.createdAt?.take(10)?.let {
                    Text(it, style = MaterialTheme.typography.labelSmall, color = TextMuted)
                }
            }
            Text(
                "+" + rupiah(c.amount),
                style = MaterialTheme.typography.titleMedium,
                color = Green,
                fontWeight = FontWeight.ExtraBold,
            )
        }
    }
}
