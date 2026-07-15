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
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ContentCopy
import androidx.compose.material.icons.filled.Devices
import androidx.compose.material.icons.filled.RestartAlt
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
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
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.License
import id.vibetool.app.ui.components.CenterLoading
import id.vibetool.app.ui.components.CenterMessage
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.IndigoLight
import id.vibetool.app.ui.theme.Red
import id.vibetool.app.ui.theme.Surface2
import id.vibetool.app.ui.theme.TextMuted
import kotlinx.coroutines.launch

@Composable
fun LicensesScreen() {
    var licenses by remember { mutableStateOf<List<License>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    var confirmResetId by remember { mutableStateOf<Long?>(null) }
    var refreshKey by remember { mutableStateOf(0) }
    val context = LocalContext.current
    val scope = rememberCoroutineScope()

    LaunchedEffect(refreshKey) {
        loading = true
        try {
            val res = ApiClient.api().licenses()
            if (res.isSuccessful) {
                licenses = res.body()?.licenses ?: emptyList()
            } else {
                error = "Gagal memuat lisensi."
            }
        } catch (e: Exception) {
            error = "Tidak bisa terhubung ke server."
        }
        loading = false
    }

    confirmResetId?.let { id ->
        AlertDialog(
            onDismissRequest = { confirmResetId = null },
            title = { Text("Reset Perangkat?") },
            text = { Text("Semua perangkat yang terkoneksi ke lisensi ini akan dilepas. Kamu bisa mengaktifkan ulang di PC/laptop lain.") },
            confirmButton = {
                TextButton(onClick = {
                    confirmResetId = null
                    scope.launch {
                        try {
                            val res = ApiClient.api().resetLicenseDevices(id)
                            Toast.makeText(
                                context,
                                res.body()?.message ?: "Perangkat berhasil direset.",
                                Toast.LENGTH_SHORT,
                            ).show()
                            refreshKey++
                        } catch (e: Exception) {
                            Toast.makeText(context, "Gagal reset perangkat.", Toast.LENGTH_SHORT).show()
                        }
                    }
                }) { Text("Reset", color = Red) }
            },
            dismissButton = {
                TextButton(onClick = { confirmResetId = null }) { Text("Batal") }
            },
        )
    }

    when {
        loading -> CenterLoading()
        error != null -> CenterMessage(error!!)
        licenses.isEmpty() -> CenterMessage("Kamu belum memiliki lisensi.\nLisensi muncul otomatis setelah membeli produk software.")
        else -> LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            items(licenses, key = { it.id }) { lic ->
                LicenseCard(
                    lic = lic,
                    onCopy = {
                        val cm = context.getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
                        cm.setPrimaryClip(ClipData.newPlainText("Lisensi", lic.key))
                        Toast.makeText(context, "Kunci lisensi disalin!", Toast.LENGTH_SHORT).show()
                    },
                    onReset = { confirmResetId = lic.id },
                )
            }
        }
    }
}

@Composable
private fun LicenseCard(lic: License, onCopy: () -> Unit, onReset: () -> Unit) {
    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Column {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Text(
                    lic.productTitle,
                    style = MaterialTheme.typography.titleMedium,
                    modifier = Modifier.weight(1f),
                )
                val (label, color) = when {
                    lic.isLifetime -> "Lifetime" to Green
                    lic.isExpired -> "Kedaluwarsa" to Red
                    else -> "Aktif" to IndigoLight
                }
                Text(
                    label,
                    style = MaterialTheme.typography.labelSmall,
                    color = color,
                    fontWeight = FontWeight.Bold,
                    modifier = Modifier
                        .background(color.copy(alpha = 0.15f), RoundedCornerShape(20.dp))
                        .padding(horizontal = 10.dp, vertical = 4.dp),
                )
            }

            Spacer(Modifier.height(12.dp))
            Row(
                verticalAlignment = Alignment.CenterVertically,
                modifier = Modifier
                    .fillMaxWidth()
                    .background(Surface2, RoundedCornerShape(10.dp))
                    .padding(start = 12.dp),
            ) {
                Text(
                    lic.key,
                    fontFamily = FontFamily.Monospace,
                    style = MaterialTheme.typography.bodyMedium,
                    modifier = Modifier.weight(1f),
                )
                IconButton(onClick = onCopy) {
                    Icon(Icons.Filled.ContentCopy, contentDescription = "Salin", tint = TextMuted)
                }
            }

            Spacer(Modifier.height(10.dp))
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(Icons.Filled.Devices, contentDescription = null, tint = TextMuted, modifier = Modifier.height(16.dp))
                Spacer(Modifier.width(6.dp))
                Text(
                    "${lic.devices.size} / ${lic.maxDevices} perangkat terkoneksi",
                    style = MaterialTheme.typography.bodyMedium,
                    color = TextMuted,
                    modifier = Modifier.weight(1f),
                )
                if (lic.devices.isNotEmpty()) {
                    TextButton(onClick = onReset) {
                        Icon(Icons.Filled.RestartAlt, contentDescription = null, tint = Red, modifier = Modifier.height(16.dp))
                        Spacer(Modifier.width(4.dp))
                        Text("Reset", color = Red, style = MaterialTheme.typography.labelLarge)
                    }
                }
            }
        }
    }
}
