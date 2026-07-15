package id.vibetool.app.ui.screens

import android.net.Uri
import android.widget.Toast
import androidx.browser.customtabs.CustomTabsIntent
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Chat
import androidx.compose.material.icons.automirrored.filled.KeyboardArrowRight
import androidx.compose.material.icons.filled.AccountBalanceWallet
import androidx.compose.material.icons.filled.Campaign
import androidx.compose.material.icons.filled.DesignServices
import androidx.compose.material.icons.filled.Description
import androidx.compose.material.icons.filled.Group
import androidx.compose.material.icons.filled.Inventory2
import androidx.compose.material.icons.filled.Lightbulb
import androidx.compose.material.icons.filled.LocalOffer
import androidx.compose.material.icons.filled.MarkEmailRead
import androidx.compose.material.icons.filled.OpenInBrowser
import androidx.compose.material.icons.filled.PlayCircle
import androidx.compose.material.icons.filled.ReceiptLong
import androidx.compose.material.icons.filled.Settings
import androidx.compose.material.icons.filled.ShoppingBag
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import id.vibetool.app.data.ApiClient
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.theme.IndigoLight
import id.vibetool.app.ui.theme.TextMuted
import kotlinx.coroutines.launch

/**
 * Tab Menu — pusat semua fitur (paritas dengan sidebar web dashboard).
 * Fitur inti dibuka native di app; fitur kompleks dibuka di web dengan
 * autologin otomatis (ditandai ikon browser kecil).
 */
@Composable
fun MenuScreen(onNavigate: (String) -> Unit) {
    var canUpload by remember { mutableStateOf(false) }
    val context = LocalContext.current
    val scope = rememberCoroutineScope()

    LaunchedEffect(Unit) {
        try {
            val me = ApiClient.api().me()
            if (me.isSuccessful) canUpload = me.body()?.user?.canUploadProduct == true
        } catch (e: Exception) { /* menu upload disembunyikan saja */ }
    }

    fun openWeb(path: String) {
        scope.launch {
            try {
                val res = ApiClient.api().webLink(path)
                val url = res.body()?.url
                if (res.isSuccessful && url != null) {
                    CustomTabsIntent.Builder().build().launchUrl(context, Uri.parse(url))
                } else {
                    Toast.makeText(context, "Gagal membuka halaman.", Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(context, "Tidak bisa terhubung ke server.", Toast.LENGTH_SHORT).show()
            }
        }
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(16.dp),
    ) {
        Text("Menu", style = MaterialTheme.typography.headlineMedium)
        Spacer(Modifier.height(14.dp))

        // ===== Native =====
        SectionLabel("Transaksi & Tim")
        GlassCard(modifier = Modifier.fillMaxWidth(), contentPadding = PaddingValues(vertical = 4.dp)) {
            Column {
                MenuRow(Icons.Filled.ShoppingBag, "Pembelian Saya") { onNavigate("purchases") }
                MenuRow(Icons.Filled.LocalOffer, "Kuponku") { onNavigate("coupons") }
                MenuRow(Icons.Filled.Group, "Tim / Downline") { onNavigate("team") }
                MenuRow(Icons.Filled.ReceiptLong, "Pembelian Tim") { onNavigate("team-purchases") }
                MenuRow(Icons.Filled.AccountBalanceWallet, "Penarikan") { onNavigate("withdrawals") }
            }
        }

        Spacer(Modifier.height(16.dp))

        // ===== Web (autologin) =====
        SectionLabel("Fitur Lainnya (dibuka di web, otomatis login)")
        GlassCard(modifier = Modifier.fillMaxWidth(), contentPadding = PaddingValues(vertical = 4.dp)) {
            Column {
                MenuRow(Icons.Filled.Campaign, "Promo & Share", isWeb = true) { openWeb("dashboard/promo") }
                MenuRow(Icons.Filled.PlayCircle, "Video Tutorial", isWeb = true) { openWeb("dashboard/video-tutorials") }
                if (canUpload) {
                    MenuRow(Icons.Filled.Inventory2, "Produk Saya", isWeb = true) { openWeb("dashboard/member-products") }
                    MenuRow(Icons.Filled.DesignServices, "Page Builder", isWeb = true) { openWeb("dashboard/page-builder") }
                    MenuRow(Icons.Filled.Description, "Template Promo Saya", isWeb = true) { openWeb("dashboard/promo-templates") }
                }
                MenuRow(Icons.AutoMirrored.Filled.Chat, "Pesan", isWeb = true) { openWeb("dashboard/messages") }
                MenuRow(Icons.Filled.Lightbulb, "Request Software", isWeb = true) { openWeb("dashboard/software-requests") }
                MenuRow(Icons.Filled.MarkEmailRead, "Verifikasi Email", isWeb = true) { openWeb("dashboard/email-verification") }
                MenuRow(Icons.Filled.Settings, "Pengaturan", isWeb = true) { openWeb("dashboard/settings") }
            }
        }

        Spacer(Modifier.height(32.dp))
    }
}

@Composable
private fun SectionLabel(text: String) {
    Text(
        text,
        style = MaterialTheme.typography.labelSmall,
        color = TextMuted,
        modifier = Modifier.padding(start = 4.dp, bottom = 6.dp),
    )
}

@Composable
private fun MenuRow(
    icon: ImageVector,
    title: String,
    isWeb: Boolean = false,
    onClick: () -> Unit,
) {
    Row(
        verticalAlignment = Alignment.CenterVertically,
        modifier = Modifier
            .fillMaxWidth()
            .clickable { onClick() }
            .padding(horizontal = 14.dp, vertical = 13.dp),
    ) {
        Icon(icon, contentDescription = null, tint = IndigoLight, modifier = Modifier.size(22.dp))
        Spacer(Modifier.width(14.dp))
        Text(title, style = MaterialTheme.typography.bodyLarge, modifier = Modifier.weight(1f))
        if (isWeb) {
            Icon(
                Icons.Filled.OpenInBrowser,
                contentDescription = "Dibuka di web",
                tint = TextMuted,
                modifier = Modifier.size(15.dp),
            )
            Spacer(Modifier.width(4.dp))
        }
        Icon(
            Icons.AutoMirrored.Filled.KeyboardArrowRight,
            contentDescription = null,
            tint = TextMuted,
            modifier = Modifier.size(20.dp),
        )
    }
}
