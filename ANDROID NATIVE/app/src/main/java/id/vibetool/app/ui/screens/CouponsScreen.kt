package id.vibetool.app.ui.screens

import android.content.ClipData
import android.content.ClipboardManager
import android.content.Context
import android.widget.Toast
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.ContentCopy
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
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.Coupon
import id.vibetool.app.ui.components.CenterLoading
import id.vibetool.app.ui.components.CenterMessage
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.components.rupiah
import id.vibetool.app.ui.theme.BgDeep
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.IndigoLight
import id.vibetool.app.ui.theme.Red
import id.vibetool.app.ui.theme.TextMuted

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CouponsScreen(onBack: () -> Unit) {
    var assigned by remember { mutableStateOf<List<Coupon>>(emptyList()) }
    var global by remember { mutableStateOf<List<Coupon>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    val context = LocalContext.current

    LaunchedEffect(Unit) {
        try {
            val res = ApiClient.api().coupons()
            if (res.isSuccessful) {
                assigned = res.body()?.assigned ?: emptyList()
                global = res.body()?.global ?: emptyList()
            } else {
                error = "Gagal memuat kupon."
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
                title = { Text("Kuponku") },
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
            assigned.isEmpty() && global.isEmpty() ->
                CenterMessage("Belum ada kupon tersedia.", Modifier.padding(padding))
            else -> LazyColumn(
                modifier = Modifier.fillMaxSize().padding(padding),
                contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(10.dp),
            ) {
                if (assigned.isNotEmpty()) {
                    item { SectionTitle("Kupon Khusus Kamu") }
                    items(assigned, key = { "a${it.id}" }) { c -> CouponCard(c, context) }
                }
                if (global.isNotEmpty()) {
                    item { SectionTitle("Kupon Umum") }
                    items(global, key = { "g${it.id}" }) { c -> CouponCard(c, context) }
                }
            }
        }
    }
}

@Composable
private fun SectionTitle(text: String) {
    Text(
        text,
        style = MaterialTheme.typography.titleMedium,
        modifier = Modifier.padding(vertical = 4.dp),
    )
}

@Composable
private fun CouponCard(c: Coupon, context: Context) {
    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Column {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Column(Modifier.weight(1f)) {
                    Text(
                        c.code,
                        fontFamily = FontFamily.Monospace,
                        fontWeight = FontWeight.ExtraBold,
                        style = MaterialTheme.typography.titleLarge,
                        color = IndigoLight,
                    )
                    val discount = if (c.discountType == "percent") {
                        "Diskon ${c.discountValue.toInt()}%"
                    } else {
                        "Diskon " + rupiah(c.discountValue)
                    }
                    Text(discount, style = MaterialTheme.typography.bodyMedium, color = Green, fontWeight = FontWeight.Bold)
                }
                Column(horizontalAlignment = Alignment.End) {
                    Text(
                        if (c.isUsable) "Aktif" else "Tidak Aktif",
                        style = MaterialTheme.typography.labelSmall,
                        color = if (c.isUsable) Green else Red,
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier
                            .background(
                                (if (c.isUsable) Green else Red).copy(alpha = 0.15f),
                                RoundedCornerShape(20.dp),
                            )
                            .padding(horizontal = 10.dp, vertical = 4.dp),
                    )
                    IconButton(onClick = {
                        val cm = context.getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
                        cm.setPrimaryClip(ClipData.newPlainText("Kupon", c.code))
                        Toast.makeText(context, "Kode kupon disalin!", Toast.LENGTH_SHORT).show()
                    }) {
                        Icon(Icons.Filled.ContentCopy, contentDescription = "Salin", tint = TextMuted)
                    }
                }
            }
            if (c.products.isNotEmpty()) {
                Text(
                    "Berlaku: " + c.products.joinToString(", "),
                    style = MaterialTheme.typography.labelSmall,
                    color = TextMuted,
                )
            }
            c.expiredAt?.take(10)?.let {
                Text("Berlaku s/d $it", style = MaterialTheme.typography.labelSmall, color = TextMuted)
            }
        }
    }
}
