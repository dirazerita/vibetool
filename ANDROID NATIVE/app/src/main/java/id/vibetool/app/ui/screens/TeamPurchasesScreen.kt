package id.vibetool.app.ui.screens

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
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.TeamPurchaseMember
import id.vibetool.app.data.TeamPurchaseStats
import id.vibetool.app.ui.components.CenterLoading
import id.vibetool.app.ui.components.CenterMessage
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.components.rupiah
import id.vibetool.app.ui.theme.Amber
import id.vibetool.app.ui.theme.BgDeep
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.IndigoLight
import id.vibetool.app.ui.theme.TextMuted

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun TeamPurchasesScreen(onBack: () -> Unit) {
    var stats by remember { mutableStateOf<TeamPurchaseStats?>(null) }
    var team by remember { mutableStateOf<List<TeamPurchaseMember>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        try {
            val res = ApiClient.api().teamPurchases()
            if (res.isSuccessful) {
                stats = res.body()?.stats
                team = res.body()?.team ?: emptyList()
            } else {
                error = "Gagal memuat data."
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
                title = { Text("Pembelian Tim") },
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
            team.isEmpty() -> CenterMessage(
                "Belum ada anggota tim.\nAjak teman lewat link afiliasimu!",
                Modifier.padding(padding),
            )
            else -> LazyColumn(
                modifier = Modifier.fillMaxSize().padding(padding),
                contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(10.dp),
            ) {
                stats?.let { s ->
                    item {
                        Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                            StatBox("Total Tim", "${s.total}", IndigoLight, Modifier.weight(1f))
                            StatBox("Sudah Beli", "${s.buyers}", Green, Modifier.weight(1f))
                            StatBox("Belum Beli", "${s.nonBuyers}", Amber, Modifier.weight(1f))
                        }
                    }
                    item {
                        GlassCard(modifier = Modifier.fillMaxWidth()) {
                            Column {
                                Text("Total Komisi dari Tim", style = MaterialTheme.typography.labelSmall, color = TextMuted)
                                Text(
                                    rupiah(s.teamCommission),
                                    style = MaterialTheme.typography.headlineMedium,
                                    color = Green,
                                    fontWeight = FontWeight.ExtraBold,
                                )
                            }
                        }
                    }
                }

                items(team, key = { it.id }) { m -> MemberRow(m) }
            }
        }
    }
}

@Composable
private fun StatBox(
    label: String,
    value: String,
    color: androidx.compose.ui.graphics.Color,
    modifier: Modifier = Modifier,
) {
    GlassCard(modifier = modifier) {
        Column(horizontalAlignment = Alignment.CenterHorizontally, modifier = Modifier.fillMaxWidth()) {
            Text(value, style = MaterialTheme.typography.headlineMedium, color = color, fontWeight = FontWeight.ExtraBold)
            Text(label, style = MaterialTheme.typography.labelSmall, color = TextMuted)
        }
    }
}

@Composable
private fun MemberRow(m: TeamPurchaseMember) {
    GlassCard(modifier = Modifier.fillMaxWidth()) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Column(Modifier.weight(1f)) {
                Text(m.name, style = MaterialTheme.typography.titleMedium, maxLines = 1)
                if (m.hasPurchased) {
                    Text(
                        "${m.purchaseCount} pembelian · ${rupiah(m.totalSpent)}",
                        style = MaterialTheme.typography.labelSmall,
                        color = TextMuted,
                    )
                    Text(
                        "Komisimu: ${rupiah(m.myCommission)}",
                        style = MaterialTheme.typography.labelSmall,
                        color = Green,
                        fontWeight = FontWeight.Bold,
                    )
                } else {
                    Text(
                        "Belum ada pembelian — follow up yuk!",
                        style = MaterialTheme.typography.labelSmall,
                        color = Amber,
                    )
                }
            }
            Spacer(Modifier.width(8.dp))
            val color = if (m.hasPurchased) Green else Amber
            Text(
                if (m.hasPurchased) "Buyer" else "Prospek",
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
