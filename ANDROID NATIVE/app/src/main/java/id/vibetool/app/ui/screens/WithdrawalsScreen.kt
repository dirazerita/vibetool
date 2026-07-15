package id.vibetool.app.ui.screens

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
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.WithdrawRequest
import id.vibetool.app.data.WithdrawalItem
import id.vibetool.app.data.WithdrawalsResponse
import id.vibetool.app.ui.components.CenterLoading
import id.vibetool.app.ui.components.CenterMessage
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.components.GradientButton
import id.vibetool.app.ui.components.rupiah
import id.vibetool.app.ui.theme.Amber
import id.vibetool.app.ui.theme.BgDeep
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.Red
import id.vibetool.app.ui.theme.TextMuted
import kotlinx.coroutines.launch
import org.json.JSONObject

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun WithdrawalsScreen(onBack: () -> Unit) {
    var info by remember { mutableStateOf<WithdrawalsResponse?>(null) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    var amountText by remember { mutableStateOf("") }
    var submitting by remember { mutableStateOf(false) }
    var formMessage by remember { mutableStateOf<Pair<String, Boolean>?>(null) } // pesan, isSuccess
    var refreshKey by remember { mutableStateOf(0) }
    val scope = rememberCoroutineScope()

    LaunchedEffect(refreshKey) {
        loading = info == null
        try {
            val res = ApiClient.api().withdrawals()
            if (res.isSuccessful) {
                info = res.body()
            } else {
                error = "Gagal memuat data penarikan."
            }
        } catch (e: Exception) {
            error = "Tidak bisa terhubung ke server."
        }
        loading = false
    }

    fun submit() {
        val amount = amountText.toDoubleOrNull()
        if (amount == null || amount <= 0) {
            formMessage = "Masukkan jumlah yang valid." to false
            return
        }
        submitting = true
        formMessage = null
        scope.launch {
            try {
                val res = ApiClient.api().requestWithdrawal(WithdrawRequest(amount))
                if (res.isSuccessful && res.body()?.ok == true) {
                    formMessage = (res.body()?.message ?: "Permintaan penarikan diajukan.") to true
                    amountText = ""
                    refreshKey++
                } else {
                    val raw = res.errorBody()?.string()
                    formMessage = (try {
                        JSONObject(raw ?: "").optString("message", "Gagal mengajukan penarikan.")
                    } catch (e: Exception) {
                        "Gagal mengajukan penarikan."
                    }) to false
                }
            } catch (e: Exception) {
                formMessage = "Tidak bisa terhubung ke server." to false
            }
            submitting = false
        }
    }

    Scaffold(
        containerColor = BgDeep,
        topBar = {
            TopAppBar(
                title = { Text("Penarikan") },
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
            error != null && info == null -> CenterMessage(error!!, Modifier.padding(padding))
            info != null -> {
                val d = info!!
                LazyColumn(
                    modifier = Modifier.fillMaxSize().padding(padding),
                    contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp),
                ) {
                    // Saldo + form
                    item {
                        GlassCard(modifier = Modifier.fillMaxWidth()) {
                            Column {
                                Text("Saldo Tersedia", style = MaterialTheme.typography.labelSmall, color = TextMuted)
                                Text(
                                    rupiah(d.balance),
                                    style = MaterialTheme.typography.headlineLarge,
                                    color = Green,
                                    fontWeight = FontWeight.ExtraBold,
                                )
                                d.bankName?.let {
                                    Text(
                                        "Rekening: $it · ${d.bankAccount}",
                                        style = MaterialTheme.typography.bodyMedium,
                                        color = TextMuted,
                                    )
                                }

                                Spacer(Modifier.height(14.dp))

                                when {
                                    !d.emailVerified -> WarnBox("Verifikasi email dulu sebelum bisa menarik komisi (menu Verifikasi Email).")
                                    !d.bankFilled -> WarnBox("Lengkapi informasi bank di menu Pengaturan dulu.")
                                    else -> {
                                        OutlinedTextField(
                                            value = amountText,
                                            onValueChange = { amountText = it.filter { ch -> ch.isDigit() } },
                                            label = { Text("Jumlah penarikan (min ${rupiah(d.minAmount)})") },
                                            singleLine = true,
                                            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                                            modifier = Modifier.fillMaxWidth(),
                                        )
                                        Spacer(Modifier.height(12.dp))
                                        GradientButton(
                                            text = "Ajukan Penarikan",
                                            onClick = { submit() },
                                            loading = submitting,
                                            modifier = Modifier.fillMaxWidth(),
                                        )
                                    }
                                }

                                formMessage?.let { (msg, ok) ->
                                    Spacer(Modifier.height(10.dp))
                                    Text(msg, color = if (ok) Green else Red, style = MaterialTheme.typography.bodyMedium)
                                }
                            }
                        }
                    }

                    // Riwayat
                    item {
                        Text("Riwayat Penarikan", style = MaterialTheme.typography.titleLarge)
                    }
                    if (d.withdrawals.isEmpty()) {
                        item {
                            Text("Belum ada penarikan.", style = MaterialTheme.typography.bodyMedium, color = TextMuted)
                        }
                    } else {
                        items(d.withdrawals, key = { it.id }) { w -> WithdrawalRow(w) }
                    }
                }
            }
        }
    }
}

@Composable
private fun WarnBox(message: String) {
    Text(
        message,
        style = MaterialTheme.typography.bodyMedium,
        color = Amber,
        modifier = Modifier
            .fillMaxWidth()
            .background(Amber.copy(alpha = 0.1f), RoundedCornerShape(10.dp))
            .padding(12.dp),
    )
}

@Composable
private fun WithdrawalRow(w: WithdrawalItem) {
    val (label, color) = when (w.status) {
        "completed", "approved" -> "Selesai" to Green
        "pending" -> "Diproses" to Amber
        "rejected" -> "Ditolak" to Red
        else -> w.status to TextMuted
    }

    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Column(Modifier.weight(1f)) {
                Text(
                    rupiah(w.amount),
                    style = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.Bold,
                )
                Text(
                    "${w.bankName ?: "—"} · ${w.bankAccount ?: "—"}",
                    style = MaterialTheme.typography.labelSmall,
                    color = TextMuted,
                )
                w.createdAt?.take(10)?.let {
                    Text(it, style = MaterialTheme.typography.labelSmall, color = TextMuted)
                }
                w.note?.let {
                    Text("Catatan: $it", style = MaterialTheme.typography.labelSmall, color = Amber)
                }
            }
            Spacer(Modifier.width(8.dp))
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
    }
}
