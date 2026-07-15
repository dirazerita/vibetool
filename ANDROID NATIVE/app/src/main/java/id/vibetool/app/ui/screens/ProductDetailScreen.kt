package id.vibetool.app.ui.screens

import android.net.Uri
import androidx.browser.customtabs.CustomTabsIntent
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
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
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
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextDecoration
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.Product
import id.vibetool.app.ui.components.CenterLoading
import id.vibetool.app.ui.components.CenterMessage
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.components.GradientButton
import id.vibetool.app.ui.components.rupiah
import id.vibetool.app.ui.theme.BgDeep
import id.vibetool.app.ui.theme.GradientPrimary
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.IndigoLight
import id.vibetool.app.ui.theme.TextMuted
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ProductDetailScreen(slug: String, onBack: () -> Unit) {
    var product by remember { mutableStateOf<Product?>(null) }
    var loading by remember { mutableStateOf(true) }
    var error by remember { mutableStateOf<String?>(null) }
    var buying by remember { mutableStateOf(false) }
    val context = LocalContext.current
    val scope = rememberCoroutineScope()

    LaunchedEffect(slug) {
        try {
            val res = ApiClient.api().product(slug)
            if (res.isSuccessful && res.body()?.ok == true) {
                product = res.body()?.product
            } else {
                error = "Produk tidak ditemukan."
            }
        } catch (e: Exception) {
            error = "Tidak bisa terhubung ke server."
        }
        loading = false
    }

    fun buyNow() {
        buying = true
        scope.launch {
            try {
                // Minta signed autologin URL lalu buka checkout di Custom Tabs.
                // Pembayaran gateway (Xendit/Pakasir) memang berbasis web redirect.
                val res = ApiClient.api().checkoutLink(slug)
                val url = res.body()?.checkoutUrl
                if (res.isSuccessful && url != null) {
                    CustomTabsIntent.Builder().build()
                        .launchUrl(context, Uri.parse(url))
                }
            } catch (e: Exception) {
                // Fallback: buka halaman produk publik
                product?.webUrl?.let {
                    CustomTabsIntent.Builder().build().launchUrl(context, Uri.parse(it))
                }
            }
            buying = false
        }
    }

    Scaffold(
        containerColor = BgDeep,
        topBar = {
            TopAppBar(
                title = { Text(product?.title ?: "Detail Produk", maxLines = 1) },
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
            product != null -> {
                val p = product!!
                Column(
                    modifier = Modifier
                        .padding(padding)
                        .fillMaxSize()
                        .verticalScroll(rememberScrollState())
                        .padding(16.dp),
                ) {
                    // Gambar produk
                    if (p.thumbnail != null) {
                        AsyncImage(
                            model = p.thumbnail,
                            contentDescription = p.title,
                            contentScale = ContentScale.Crop,
                            modifier = Modifier
                                .fillMaxWidth()
                                .aspectRatio(1f)
                                .background(Color(0xFF0D1326), RoundedCornerShape(18.dp)),
                        )
                    } else {
                        Box(
                            modifier = Modifier
                                .fillMaxWidth()
                                .aspectRatio(16f / 9f)
                                .background(GradientPrimary, RoundedCornerShape(18.dp)),
                            contentAlignment = Alignment.Center,
                        ) {
                            Text(p.title, style = MaterialTheme.typography.titleLarge, color = Color.White)
                        }
                    }

                    Spacer(Modifier.height(18.dp))
                    Text(p.title, style = MaterialTheme.typography.headlineMedium)
                    Spacer(Modifier.height(8.dp))
                    Text(p.description, style = MaterialTheme.typography.bodyLarge, color = TextMuted)

                    Spacer(Modifier.height(18.dp))
                    GlassCard(modifier = Modifier.fillMaxWidth()) {
                        Column {
                            Text("Harga", style = MaterialTheme.typography.labelSmall, color = TextMuted)
                            Spacer(Modifier.height(4.dp))
                            if (p.isFree) {
                                Text("GRATIS", style = MaterialTheme.typography.headlineMedium,
                                    color = Green, fontWeight = FontWeight.ExtraBold)
                            } else {
                                p.compareAtPrice?.let {
                                    Text(
                                        rupiah(it),
                                        style = MaterialTheme.typography.bodyMedium,
                                        color = TextMuted,
                                        textDecoration = TextDecoration.LineThrough,
                                    )
                                }
                                Row(verticalAlignment = Alignment.Bottom) {
                                    if (p.hasPackages) {
                                        Text("Mulai ", style = MaterialTheme.typography.bodyMedium, color = TextMuted)
                                    }
                                    Text(
                                        rupiah(p.price),
                                        style = MaterialTheme.typography.headlineMedium,
                                        color = IndigoLight,
                                        fontWeight = FontWeight.ExtraBold,
                                    )
                                }
                            }
                        }
                    }

                    // Paket harga (kalau ada)
                    p.packages?.takeIf { it.isNotEmpty() }?.let { pkgs ->
                        Spacer(Modifier.height(12.dp))
                        GlassCard(modifier = Modifier.fillMaxWidth()) {
                            Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                                Text("Pilihan Paket", style = MaterialTheme.typography.titleMedium)
                                pkgs.forEach { pkg ->
                                    Row(
                                        modifier = Modifier.fillMaxWidth(),
                                        horizontalArrangement = Arrangement.SpaceBetween,
                                    ) {
                                        Text(pkg.name, style = MaterialTheme.typography.bodyMedium)
                                        Text(
                                            rupiah(pkg.price),
                                            style = MaterialTheme.typography.bodyMedium,
                                            color = IndigoLight,
                                            fontWeight = FontWeight.Bold,
                                        )
                                    }
                                }
                            }
                        }
                    }

                    Spacer(Modifier.height(24.dp))
                    GradientButton(
                        text = if (p.isFree) "Dapatkan Gratis" else "Beli Sekarang",
                        onClick = { buyNow() },
                        loading = buying,
                        modifier = Modifier.fillMaxWidth(),
                    )
                    Spacer(Modifier.height(8.dp))
                    Text(
                        "Pembayaran aman via browser (Xendit / transfer).",
                        style = MaterialTheme.typography.labelSmall,
                        color = TextMuted,
                        modifier = Modifier.align(Alignment.CenterHorizontally),
                    )
                    Spacer(Modifier.height(32.dp))
                }
            }
        }
    }
}
