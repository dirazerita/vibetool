package id.vibetool.app.ui.screens

import android.content.ClipData
import android.content.ClipboardManager
import android.content.Context
import android.widget.Toast
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.GridItemSpan
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ContentCopy
import androidx.compose.material.icons.filled.Group
import androidx.compose.material.icons.filled.ShoppingBag
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
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
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.DashboardSummary
import id.vibetool.app.data.Product
import id.vibetool.app.ui.components.CenterLoading
import id.vibetool.app.ui.components.CenterMessage
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.components.rupiah
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.GradientHero
import id.vibetool.app.ui.theme.GradientPrimary
import id.vibetool.app.ui.theme.IndigoLight
import id.vibetool.app.ui.theme.TextMuted

@Composable
fun HomeScreen(onOpenProduct: (String) -> Unit) {
    var summary by remember { mutableStateOf<DashboardSummary?>(null) }
    var products by remember { mutableStateOf<List<Product>>(emptyList()) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    val context = LocalContext.current

    LaunchedEffect(Unit) {
        try {
            val dash = ApiClient.api().dashboard()
            if (dash.isSuccessful) summary = dash.body()?.summary

            val prods = ApiClient.api().products()
            if (prods.isSuccessful) {
                products = prods.body()?.products ?: emptyList()
            } else {
                error = "Gagal memuat produk."
            }
        } catch (e: Exception) {
            error = "Tidak bisa terhubung ke server."
        }
        loading = false
    }

    when {
        loading -> CenterLoading()
        error != null && products.isEmpty() -> CenterMessage(error!!)
        else -> LazyVerticalGrid(
            columns = GridCells.Fixed(2),
            modifier = Modifier.fillMaxSize(),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(16.dp),
            horizontalArrangement = Arrangement.spacedBy(12.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            // Header saldo & referral — span 2 kolom
            summary?.let { s ->
                item(span = { GridItemSpan(2) }) {
                    BalanceHeader(s) { link ->
                        val cm = context.getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
                        cm.setPrimaryClip(ClipData.newPlainText("Link Afiliasi", link))
                        Toast.makeText(context, "Link afiliasi disalin!", Toast.LENGTH_SHORT).show()
                    }
                }
            }

            item(span = { GridItemSpan(2) }) {
                Text(
                    "Produk Digital",
                    style = MaterialTheme.typography.titleLarge,
                    modifier = Modifier.padding(top = 8.dp, bottom = 2.dp),
                )
            }

            items(products, key = { it.id }) { product ->
                ProductCard(product) { onOpenProduct(product.slug) }
            }
        }
    }
}

@Composable
private fun BalanceHeader(s: DashboardSummary, onCopyLink: (String) -> Unit) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .background(GradientHero, RoundedCornerShape(20.dp))
            .padding(20.dp),
    ) {
        Column {
            Text("Saldo Komisi", style = MaterialTheme.typography.labelSmall, color = Color(0xFFC7D2FE))
            Text(
                rupiah(s.balance),
                style = MaterialTheme.typography.headlineLarge,
                color = Color.White,
            )
            Spacer(Modifier.height(14.dp))
            Row(verticalAlignment = Alignment.CenterVertically) {
                StatChip(Icons.Filled.Group, "${s.teamCount} Tim")
                Spacer(Modifier.width(8.dp))
                StatChip(Icons.Filled.ShoppingBag, "${s.purchaseCount} Pembelian")
            }
            s.referralLink?.let { link ->
                Spacer(Modifier.height(14.dp))
                Row(
                    verticalAlignment = Alignment.CenterVertically,
                    modifier = Modifier
                        .fillMaxWidth()
                        .background(Color(0x33FFFFFF), RoundedCornerShape(12.dp))
                        .padding(start = 14.dp, top = 2.dp, bottom = 2.dp, end = 2.dp),
                ) {
                    Text(
                        link.removePrefix("https://").removePrefix("http://"),
                        style = MaterialTheme.typography.bodyMedium,
                        color = Color.White,
                        maxLines = 1,
                        overflow = TextOverflow.Ellipsis,
                        modifier = Modifier.weight(1f),
                    )
                    IconButton(onClick = { onCopyLink(link) }) {
                        Icon(Icons.Filled.ContentCopy, contentDescription = "Salin", tint = Color.White)
                    }
                }
            }
        }
    }
}

@Composable
private fun StatChip(icon: androidx.compose.ui.graphics.vector.ImageVector, label: String) {
    Row(
        verticalAlignment = Alignment.CenterVertically,
        modifier = Modifier
            .background(Color(0x26FFFFFF), RoundedCornerShape(20.dp))
            .padding(horizontal = 12.dp, vertical = 6.dp),
    ) {
        Icon(icon, contentDescription = null, tint = Color.White, modifier = Modifier.height(15.dp))
        Spacer(Modifier.width(6.dp))
        Text(label, style = MaterialTheme.typography.labelSmall, color = Color.White)
    }
}

@Composable
private fun ProductCard(product: Product, onClick: () -> Unit) {
    GlassCard(
        modifier = Modifier.fillMaxWidth(),
        contentPadding = androidx.compose.foundation.layout.PaddingValues(0.dp),
        onClick = onClick,
    ) {
        Column {
            if (product.thumbnail != null) {
                AsyncImage(
                    model = product.thumbnail,
                    contentDescription = product.title,
                    contentScale = ContentScale.Crop,
                    modifier = Modifier
                        .fillMaxWidth()
                        .aspectRatio(1f)
                        .clip(RoundedCornerShape(topStart = 18.dp, topEnd = 18.dp)),
                )
            } else {
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .aspectRatio(1f)
                        .clip(RoundedCornerShape(topStart = 18.dp, topEnd = 18.dp))
                        .background(GradientPrimary),
                    contentAlignment = Alignment.Center,
                ) {
                    Text(
                        product.title,
                        style = MaterialTheme.typography.titleMedium,
                        color = Color.White,
                        textAlign = androidx.compose.ui.text.style.TextAlign.Center,
                        modifier = Modifier.padding(12.dp),
                    )
                }
            }
            Column(Modifier.padding(12.dp)) {
                Text(
                    product.title,
                    style = MaterialTheme.typography.titleMedium,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis,
                )
                Spacer(Modifier.height(6.dp))
                if (product.isFree) {
                    Text("GRATIS", color = Green, fontWeight = FontWeight.ExtraBold,
                        style = MaterialTheme.typography.titleMedium)
                } else {
                    if (product.hasPackages) {
                        Text("Mulai", style = MaterialTheme.typography.labelSmall, color = TextMuted)
                    }
                    Text(
                        rupiah(product.price),
                        color = IndigoLight,
                        fontWeight = FontWeight.ExtraBold,
                        style = MaterialTheme.typography.titleMedium,
                    )
                }
            }
        }
    }
}
