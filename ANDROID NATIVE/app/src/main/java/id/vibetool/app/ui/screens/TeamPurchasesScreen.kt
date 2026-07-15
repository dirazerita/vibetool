package id.vibetool.app.ui.screens

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.ExpandLess
import androidx.compose.material.icons.filled.ExpandMore
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
import androidx.compose.ui.graphics.Color
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
import id.vibetool.app.ui.theme.Surface2
import id.vibetool.app.ui.theme.TextMuted

/** Filter daftar downline berdasarkan kotak statistik yang diklik. */
private enum class TeamFilter { ALL, BUYERS, NON_BUYERS }

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun TeamPurchasesScreen(onBack: () -> Unit) {
    var stats by remember { mutableStateOf<TeamPurchaseStats?>(null) }
    var team by remember { mutableStateOf<List<TeamPurchaseMember>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    var filter by remember { mutableStateOf(TeamFilter.ALL) }
    var expandedId by remember { mutableStateOf<Long?>(null) }

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

    val filteredTeam = when (filter) {
        TeamFilter.ALL -> team
        TeamFilter.BUYERS -> team.filter { it.hasPurchased }
        TeamFilter.NON_BUYERS -> team.filter { !it.hasPurchased }
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
                            StatBox("Total Tim", "${s.total}", IndigoLight,
                                selected = filter == TeamFilter.ALL,
                                modifier = Modifier.weight(1f)) { filter = TeamFilter.ALL }
                            StatBox("Sudah Beli", "${s.buyers}", Green,
                                selected = filter == TeamFilter.BUYERS,
                                modifier = Modifier.weight(1f)) { filter = TeamFilter.BUYERS }
                            StatBox("Belum Beli", "${s.nonBuyers}", Amber,
                                selected = filter == TeamFilter.NON_BUYERS,
                                modifier = Modifier.weight(1f)) { filter = TeamFilter.NON_BUYERS }
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
                    // Label filter aktif
                    item {
                        val label = when (filter) {
                            TeamFilter.ALL -> "Semua anggota tim (${filteredTeam.size})"
                            TeamFilter.BUYERS -> "Yang sudah beli (${filteredTeam.size})"
                            TeamFilter.NON_BUYERS -> "Yang belum beli (${filteredTeam.size}) — layak di-follow up!"
                        }
                        Text(label, style = MaterialTheme.typography.titleMedium)
                    }
                }

                if (filteredTeam.isEmpty()) {
                    item {
                        Text(
                            "Tidak ada anggota di kategori ini.",
                            style = MaterialTheme.typography.bodyMedium,
                            color = TextMuted,
                        )
                    }
                } else {
                    items(filteredTeam, key = { it.id }) { m ->
                        MemberRow(
                            m = m,
                            expanded = expandedId == m.id,
                            onToggle = { expandedId = if (expandedId == m.id) null else m.id },
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun StatBox(
    label: String,
    value: String,
    color: Color,
    selected: Boolean,
    modifier: Modifier = Modifier,
    onClick: () -> Unit,
) {
    var boxModifier = modifier
        .background(Surface2, RoundedCornerShape(18.dp))
        .clickable { onClick() }
    if (selected) {
        boxModifier = boxModifier.border(2.dp, color, RoundedCornerShape(18.dp))
    }

    Column(
        horizontalAlignment = Alignment.CenterHorizontally,
        modifier = boxModifier.padding(vertical = 16.dp),
    ) {
        Text(value, style = MaterialTheme.typography.headlineMedium, color = color, fontWeight = FontWeight.ExtraBold)
        Text(label, style = MaterialTheme.typography.labelSmall, color = TextMuted)
    }
}

@Composable
private fun MemberRow(
    m: TeamPurchaseMember,
    expanded: Boolean,
    onToggle: () -> Unit,
) {
    GlassCard(modifier = Modifier.fillMaxWidth(), onClick = onToggle) {
        Column {
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
                if (m.hasPurchased) {
                    Spacer(Modifier.width(4.dp))
                    Icon(
                        if (expanded) Icons.Filled.ExpandLess else Icons.Filled.ExpandMore,
                        contentDescription = if (expanded) "Tutup detail" else "Lihat detail",
                        tint = TextMuted,
                        modifier = Modifier.size(20.dp),
                    )
                }
            }

            // ===== Detail pembelian (expand saat kartu diklik) =====
            AnimatedVisibility(visible = expanded && m.purchases.isNotEmpty()) {
                Column {
                    Spacer(Modifier.height(10.dp))
                    Column(
                        verticalArrangement = Arrangement.spacedBy(8.dp),
                        modifier = Modifier
                            .fillMaxWidth()
                            .background(Surface2, RoundedCornerShape(12.dp))
                            .padding(12.dp),
                    ) {
                        Text(
                            "Detail Pembelian",
                            style = MaterialTheme.typography.labelSmall,
                            color = TextMuted,
                            fontWeight = FontWeight.Bold,
                        )
                        m.purchases.forEach { p ->
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Icon(
                                    Icons.Filled.ShoppingBag,
                                    contentDescription = null,
                                    tint = IndigoLight,
                                    modifier = Modifier.size(16.dp),
                                )
                                Spacer(Modifier.width(8.dp))
                                Column(Modifier.weight(1f)) {
                                    Text(p.productTitle, style = MaterialTheme.typography.bodyMedium, maxLines = 1)
                                    p.date?.take(10)?.let {
                                        Text(it, style = MaterialTheme.typography.labelSmall, color = TextMuted)
                                    }
                                }
                                Text(
                                    rupiah(p.amount),
                                    style = MaterialTheme.typography.bodyMedium,
                                    color = IndigoLight,
                                    fontWeight = FontWeight.Bold,
                                )
                            }
                        }
                    }
                }
            }
        }
    }
}
