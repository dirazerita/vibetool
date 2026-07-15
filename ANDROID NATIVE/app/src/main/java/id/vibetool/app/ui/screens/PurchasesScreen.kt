package id.vibetool.app.ui.screens

import android.net.Uri
import androidx.browser.customtabs.CustomTabsIntent
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Download
import androidx.compose.material.icons.filled.ShoppingBag
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.Purchase
import id.vibetool.app.ui.components.CenterLoading
import id.vibetool.app.ui.components.CenterMessage
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.components.rupiah
import id.vibetool.app.ui.theme.Amber
import id.vibetool.app.ui.theme.BgDeep
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.IndigoLight
import id.vibetool.app.ui.theme.Red
import id.vibetool.app.ui.theme.TextMuted

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun PurchasesScreen(onBack: () -> Unit) {
    var purchases by remember { mutableStateOf<List<Purchase>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    val context = LocalContext.current

    LaunchedEffect(Unit) {
        try {
            val res = ApiClient.api().purchases()
            if (res.isSuccessful) {
                purchases = res.body()?.purchases ?: emptyList()
            } else {
                error = "Gagal memuat pembelian."
            }
        } catch (e: Exception) {
            error = "Tidak bisa terhubung ke server."
        }
        loading = false
    }

    Scaffold(
        containerColor = BgDeep,
        topBar = {
            TopAppBar(
                title = { Text("Pembelian Saya") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = BgDeep),
            )
        },
    ) { padding ->
        when {
            loading -> CenterLoading(Modifier.padding(padding))
            error != null -> CenterMessage(error!!, Modifier.padding(padding))
            purchases.isEmpty() -> CenterMessage(
                "Belum ada pembelian.\nJelajahi produk di Beranda!",
                Modifier.padding(padding),
            )
            else -> LazyColumn(
                modifier = Modifier.fillMaxSize().padding(padding),
                contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(10.dp),
            ) {
                items(purchases, key = { it.id }) { p ->
                    PurchaseRow(p) { url ->
                        CustomTabsIntent.Builder().build().launchUrl(context, Uri.parse(url))
                    }
                }
            }
        }
    }
}

@Composable
private fun PurchaseRow(p: Purchase, onOpenUrl: (String) -> Unit) {
    val statusColor = when (p.status) {
        "paid" -> Green
        "pending" -> Amber
        else -> Red
    }
    val statusLabel = when (p.status) {
        "paid" -> "Lunas"
        "pending" -> "Menunggu"
        "expired" -> "Kedaluwarsa"
        else -> p.status
    }

    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Box(
                modifier = Modifier
                    .size(42.dp)
                    .background(IndigoLight.copy(alpha = 0.15f), CircleShape),
                contentAlignment = Alignment.Center,
            ) {
                Icon(Icons.Filled.ShoppingBag, contentDescription = null, tint = IndigoLight, modifier = Modifier.size(22.dp))
            }
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(p.productTitle, style = MaterialTheme.typography.titleMedium, maxLines = 1)
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Text(rupiah(p.amount), style = MaterialTheme.typography.bodyMedium, color = TextMuted)
                    Spacer(Modifier.width(8.dp))
                    Text(
                        statusLabel,
                        style = MaterialTheme.typography.labelSmall,
                        color = statusColor,
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier
                            .background(statusColor.copy(alpha = 0.15f), RoundedCornerShape(20.dp))
                            .padding(horizontal = 8.dp, vertical = 2.dp),
                    )
                }
                p.createdAt?.take(10)?.let {
                    Text(it, style = MaterialTheme.typography.labelSmall, color = TextMuted)
                }
            }
            p.downloadUrl?.let { url ->
                IconButton(onClick = { onOpenUrl(url) }) {
                    Icon(Icons.Filled.Download, contentDescription = "Download", tint = Green)
                }
            }
        }
    }
}
