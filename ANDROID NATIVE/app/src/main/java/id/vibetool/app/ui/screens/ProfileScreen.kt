package id.vibetool.app.ui.screens

import android.net.Uri
import androidx.browser.customtabs.CustomTabsIntent
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
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
import androidx.compose.material.icons.filled.AccountBalanceWallet
import androidx.compose.material.icons.filled.Language
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

@Composable
fun ProfileScreen(
    onLoggedOut: () -> Unit,
    onOpenWithdrawals: () -> Unit = {},
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
                    Box(
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
                        Text("Kode Referral: $it", style = MaterialTheme.typography.labelSmall, color = IndigoLight)
                    }
                }
            }
        }

        Spacer(Modifier.height(12.dp))

        // Saldo — tap untuk ke halaman Penarikan (native)
        user?.let { u ->
            GlassCard(
                modifier = Modifier.fillMaxWidth(),
                onClick = onOpenWithdrawals,
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
            Spacer(Modifier.height(12.dp))
        }

        // Buka website
        GlassCard(
            modifier = Modifier.fillMaxWidth(),
            onClick = {
                CustomTabsIntent.Builder().build()
                    .launchUrl(context, Uri.parse(ApiClient.BASE_URL))
            },
        ) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Filled.Language, contentDescription = null, tint = IndigoLight)
                Spacer(Modifier.width(12.dp))
                Column {
                    Text("Buka Website VibeTool.Id", style = MaterialTheme.typography.titleMedium)
                    Text(
                        "Fitur lengkap lainnya ada di tab Menu",
                        style = MaterialTheme.typography.labelSmall,
                        color = TextMuted,
                    )
                }
            }
        }

        Spacer(Modifier.height(12.dp))

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
