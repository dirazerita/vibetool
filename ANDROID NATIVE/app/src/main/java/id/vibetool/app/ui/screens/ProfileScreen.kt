package id.vibetool.app.ui.screens

import android.net.Uri
import android.widget.Toast
import androidx.browser.customtabs.CustomTabsIntent
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.automirrored.filled.Chat
import androidx.compose.material.icons.automirrored.filled.KeyboardArrowRight
import androidx.compose.material.icons.filled.AccountBalanceWallet
import androidx.compose.material.icons.filled.Campaign
import androidx.compose.material.icons.filled.DesignServices
import androidx.compose.material.icons.filled.Description
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
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.User
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.components.rupiah
import id.vibetool.app.ui.theme.GradientPrimary
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.IndigoLight
import id.vibetool.app.ui.theme.Red
import id.vibetool.app.ui.theme.TextMuted
import kotlinx.coroutines.launch

/** Item menu: kalau webPath != null dibuka di web (autologin), selain itu aksi native. */
private data class MenuItem(
    val icon: ImageVector,
    val title: String,
    val webPath: String? = null,
    val needsUpload: Boolean = false,
)

private val menuItems = listOf(
    MenuItem(Icons.Filled.ShoppingBag, "Pembelian Saya"),                      // native
    MenuItem(Icons.Filled.LocalOffer, "Kuponku", "dashboard/coupons"),
    MenuItem(Icons.Filled.Campaign, "Promo & Share", "dashboard/promo"),
    MenuItem(Icons.Filled.ReceiptLong, "Pembelian Tim", "dashboard/team-purchases"),
    MenuItem(Icons.Filled.PlayCircle, "Video Tutorial", "dashboard/video-tutorials"),
    MenuItem(Icons.Filled.Inventory2, "Produk Saya", "dashboard/member-products", needsUpload = true),
    MenuItem(Icons.Filled.DesignServices, "Page Builder", "dashboard/page-builder", needsUpload = true),
    MenuItem(Icons.Filled.Description, "Template Promo Saya", "dashboard/promo-templates", needsUpload = true),
    MenuItem(Icons.Filled.AccountBalanceWallet, "Penarikan", "dashboard/withdrawals"),
    MenuItem(Icons.AutoMirrored.Filled.Chat, "Pesan", "dashboard/messages"),
    MenuItem(Icons.Filled.Lightbulb, "Request Software", "dashboard/software-requests"),
    MenuItem(Icons.Filled.MarkEmailRead, "Verifikasi Email", "dashboard/email-verification"),
    MenuItem(Icons.Filled.Settings, "Pengaturan", "dashboard/settings"),
)

@Composable
fun ProfileScreen(
    onLoggedOut: () -> Unit,
    onOpenPurchases: () -> Unit = {},
) {
    var user by remember { mutableStateOf<User?>(null) }
    var loggingOut by remember { mutableStateOf(false) }
    val context = LocalContext.current
    val scope = rememberCoroutineScope()

    LaunchedEffect(Unit) {
        try {
            val me = ApiClient.api().me()
            if (me.isSuccessful) user = me.body()?.user
        } catch (e: Exception) {
            // Layar tetap tampil dengan data seadanya.
        }
    }

    /** Buka halaman dashboard web dengan autologin (signed URL 15 menit). */
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

    fun doLogout() {
        loggingOut = true
        scope.launch {
            try {
                ApiClient.api().logout()
            } catch (e: Exception) {
                // Token dihapus lokal apa pun hasilnya.
            }
            ApiClient.tokens().clear()
            onLoggedOut()
        }
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(16.dp),
    ) {
        // Header profil
        GlassCard(modifier = Modifier.fillMaxWidth()) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                if (user?.profilePhoto != null) {
                    AsyncImage(
                        model = user!!.profilePhoto,
                        contentDescription = null,
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.size(64.dp).background(GradientPrimary, CircleShape),
                    )
                } else {
                    androidx.compose.foundation.layout.Box(
                        modifier = Modifier.size(64.dp).background(GradientPrimary, CircleShape),
                        contentAlignment = Alignment.Center,
                    ) {
                        Text(
                            (user?.name ?: ApiClient.tokens().userName ?: "?").take(1).uppercase(),
                            style = MaterialTheme.typography.headlineMedium,
                            color = Color.White,
                        )
                    }
                }
                Spacer(Modifier.width(14.dp))
                Column {
                    Text(
                        user?.name ?: ApiClient.tokens().userName ?: "Member",
                        style = MaterialTheme.typography.titleLarge,
                    )
                    Text(user?.email ?: "", style = MaterialTheme.typography.bodyMedium, color = TextMuted)
                    user?.referralCode?.let {
                        Text("Kode: $it", style = MaterialTheme.typography.labelSmall, color = IndigoLight)
                    }
                }
            }
        }

        Spacer(Modifier.height(12.dp))

        // Saldo (tap -> Penarikan di web)
        user?.let { u ->
            GlassCard(
                modifier = Modifier.fillMaxWidth(),
                onClick = { openWeb("dashboard/withdrawals") },
            ) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Icon(Icons.Filled.AccountBalanceWallet, contentDescription = null, tint = Green)
                    Spacer(Modifier.width(12.dp))
                    Column(Modifier.weight(1f)) {
                        Text("Saldo Komisi", style = MaterialTheme.typography.labelSmall, color = TextMuted)
                        Text(
                            rupiah(u.balance),
                            style = MaterialTheme.typography.titleLarge,
                            color = Green,
                            fontWeight = FontWeight.ExtraBold,
                        )
                    }
                    Text("Tarik Saldo →", style = MaterialTheme.typography.labelLarge, color = IndigoLight)
                }
            }
            Spacer(Modifier.height(16.dp))
        }

        // ===== Menu lengkap (paritas dengan sidebar web) =====
        Text(
            "Menu",
            style = MaterialTheme.typography.titleLarge,
            modifier = Modifier.padding(vertical = 6.dp),
        )

        val canUpload = user?.canUploadProduct == true
        GlassCard(
            modifier = Modifier.fillMaxWidth(),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(vertical = 4.dp),
        ) {
            Column {
                menuItems
                    .filter { !it.needsUpload || canUpload }
                    .forEach { item ->
                        MenuRow(
                            icon = item.icon,
                            title = item.title,
                            isWeb = item.webPath != null,
                            onClick = {
                                if (item.webPath != null) openWeb(item.webPath)
                                else onOpenPurchases()
                            },
                        )
                    }
            }
        }

        Spacer(Modifier.height(16.dp))

        // Logout
        GlassCard(
            modifier = Modifier.fillMaxWidth(),
            onClick = { if (!loggingOut) doLogout() },
        ) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.AutoMirrored.Filled.Logout, contentDescription = null, tint = Red)
                Spacer(Modifier.width(12.dp))
                Text(
                    if (loggingOut) "Keluar..." else "Keluar dari Akun",
                    style = MaterialTheme.typography.titleMedium,
                    color = Red,
                )
            }
        }

        Spacer(Modifier.height(32.dp))
    }
}

@Composable
private fun MenuRow(icon: ImageVector, title: String, isWeb: Boolean, onClick: () -> Unit) {
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
