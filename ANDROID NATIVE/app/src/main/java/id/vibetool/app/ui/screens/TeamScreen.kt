package id.vibetool.app.ui.screens

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
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.TeamMember
import id.vibetool.app.ui.components.CenterLoading
import id.vibetool.app.ui.components.CenterMessage
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.theme.Amber
import id.vibetool.app.ui.theme.GradientPrimary
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.TextMuted

@Composable
fun TeamScreen() {
    var team by remember { mutableStateOf<List<TeamMember>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(Unit) {
        try {
            val res = ApiClient.api().team()
            if (res.isSuccessful) {
                team = res.body()?.team ?: emptyList()
            } else {
                error = "Gagal memuat data tim."
            }
        } catch (e: Exception) {
            error = "Tidak bisa terhubung ke server."
        }
        loading = false
    }

    when {
        loading -> CenterLoading()
        error != null -> CenterMessage(error!!)
        team.isEmpty() -> CenterMessage("Belum ada anggota tim.\nAjak teman lewat link afiliasimu — mereka jadi downline-mu!")
        else -> LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp),
        ) {
            items(team, key = { it.id }) { member ->
                GlassCard(modifier = Modifier.fillMaxWidth()) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Box(
                            modifier = Modifier
                                .size(42.dp)
                                .background(GradientPrimary, CircleShape),
                            contentAlignment = Alignment.Center,
                        ) {
                            Text(
                                member.name.take(1).uppercase(),
                                color = Color.White,
                                fontWeight = FontWeight.Bold,
                                style = MaterialTheme.typography.titleMedium,
                            )
                        }
                        Spacer(Modifier.width(12.dp))
                        Column(Modifier.weight(1f)) {
                            Text(member.name, style = MaterialTheme.typography.titleMedium, maxLines = 1)
                            member.joinedAt?.take(10)?.let {
                                Text("Bergabung $it", style = MaterialTheme.typography.labelSmall, color = TextMuted)
                            }
                        }
                        val active = member.status == "active"
                        Text(
                            if (active) "Aktif" else "Pending",
                            style = MaterialTheme.typography.labelSmall,
                            color = if (active) Green else Amber,
                            fontWeight = FontWeight.Bold,
                            modifier = Modifier
                                .background(
                                    (if (active) Green else Amber).copy(alpha = 0.15f),
                                    RoundedCornerShape(20.dp),
                                )
                                .padding(horizontal = 10.dp, vertical = 4.dp),
                        )
                    }
                }
            }
        }
    }
}
